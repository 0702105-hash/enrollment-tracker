import numpy as np
import pandas as pd
import mysql.connector
from sklearn.metrics import mean_absolute_error, mean_squared_error, r2_score
from sklearn.compose import TransformedTargetRegressor
from sklearn.pipeline import Pipeline
from sklearn.preprocessing import StandardScaler
from xgboost import XGBRegressor


DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'casDB'
}


def is_valid_number(value):
    try:
        return np.isfinite(float(value))
    except (TypeError, ValueError):
        return False


class ModelEvaluator:
    @staticmethod
    def calculate_metrics(y_true, y_pred):
        y_true = np.array(y_true, dtype=float)
        y_pred_raw = np.array(y_pred, dtype=float)
        y_pred_clipped = np.maximum(y_pred_raw, 0)

        if len(y_true) == 0 or len(y_pred_raw) == 0:
            return {
                'MAE': np.nan,
                'RMSE': np.nan,
                'MAPE': np.nan,
                'MSE': np.nan,
                'Raw_R2': np.nan,
                'Clipped_R2': np.nan
            }

        mae = mean_absolute_error(y_true, y_pred_clipped)
        mse = mean_squared_error(y_true, y_pred_clipped)
        rmse = np.sqrt(mse)

        mask = y_true != 0
        if mask.any():
            mape = np.mean(np.abs((y_true[mask] - y_pred_clipped[mask]) / y_true[mask])) * 100
        else:
            mape = np.nan

        # Variance check avoids undefined R2 results.
        if np.var(y_true) == 0:
            r2_raw = np.nan
            r2_clipped = np.nan
        else:
            try:
                r2_raw = r2_score(y_true, y_pred_raw)
            except Exception:
                r2_raw = np.nan

            try:
                r2_clipped = r2_score(y_true, y_pred_clipped)
            except Exception:
                r2_clipped = np.nan

        return {
            'MAE': round(float(mae), 2) if is_valid_number(mae) else np.nan,
            'RMSE': round(float(rmse), 2) if is_valid_number(rmse) else np.nan,
            'MAPE': round(float(mape), 2) if is_valid_number(mape) else np.nan,
            'MSE': round(float(mse), 2) if is_valid_number(mse) else np.nan,
            'Raw_R2': round(float(r2_raw), 4) if is_valid_number(r2_raw) else np.nan,
            'Clipped_R2': round(float(r2_clipped), 4) if is_valid_number(r2_clipped) else np.nan
        }


def explain_r2(raw_r2, clipped_r2):
    print("\nWhat to look for in the results")

    if is_valid_number(raw_r2) and is_valid_number(clipped_r2):
        # Case 1: clipping likely damages score.
        if raw_r2 > clipped_r2 and abs(raw_r2 - clipped_r2) >= 0.5:
            print(
                "If Raw_R2 is near 0 but Clipped_R2 is much worse, "
                "the model may be okay and clipping is damaging the R2 math."
            )
        # Case 2: both are strongly negative.
        if raw_r2 <= -1.0 and clipped_r2 <= -1.0:
            print(
                "Both Raw_R2 and Clipped_R2 are deeply negative. "
                "This usually means very weak predictive power on this split."
            )
        if raw_r2 > -1.0 and clipped_r2 > -1.0:
            print("R2 scores are not deeply negative. Model fit is weak-to-moderate, not catastrophic.")
    else:
        print("R2 is undefined for this sample (likely zero variance in y_true).")


def check_seasonality_from_db():
    print("\nSeasonality check (enrollments by semester)")

    conn = mysql.connector.connect(**DB_CONFIG)
    try:
        df = pd.read_sql(
            """
            SELECT program_id, academic_year, semester, (male + female) AS total
            FROM enrollments
            ORDER BY program_id, academic_year, semester
            """,
            conn
        )
    finally:
        conn.close()

    if df.empty:
        print("No enrollment rows found.")
        return

    sem_means = df.groupby('semester')['total'].mean().sort_index()
    sem_counts = df.groupby('semester')['total'].count().sort_index()

    print("Average total enrollment by semester:")
    for sem, avg in sem_means.items():
        print(f"  Semester {int(sem)}: mean={avg:.2f}, n={int(sem_counts.loc[sem])}")

    if len(sem_means) >= 2:
        top_sem = int(sem_means.idxmax())
        bottom_sem = int(sem_means.idxmin())
        spread = float(sem_means.max() - sem_means.min())
        print(
            f"Seasonal spread detected: Semester {top_sem} is highest, "
            f"Semester {bottom_sem} is lowest, spread={spread:.2f}."
        )
        print(
            "If your model is missing this cycle, add stronger seasonal features "
            "or increase seasonality capacity in the model."
        )


def add_enrollment_time_features(df, target_col='total'):
    """Create cyclical semester/month signals and same-semester-last-year lag.

    Expected columns include:
    - program_id
    - academic_year (e.g., 2025-2026)
    - semester (1, 2, 3)
    - target_col (default: total)
    Optional:
    - month (1..12). If missing, inferred from semester anchor months.
    """
    out = df.copy()

    if target_col not in out.columns:
        raise ValueError(f"Missing target column: {target_col}")

    if 'semester' not in out.columns:
        raise ValueError("Missing required column: semester")

    # Parse start year for proper chronological ordering.
    if 'academic_year' in out.columns:
        out['year_start'] = out['academic_year'].astype(str).str.split('-').str[0].astype(int)
    elif 'year_start' not in out.columns:
        raise ValueError("Provide either academic_year or year_start")

    # If month is not provided, map semester to representative months.
    # Semester 1 ~ Sep, Semester 2 ~ Jan, Semester 3 ~ Jun (summer).
    if 'month' not in out.columns:
        semester_to_month = {1: 9, 2: 1, 3: 6}
        out['month'] = out['semester'].map(semester_to_month).fillna(1).astype(int)

    out['semester'] = out['semester'].astype(int)
    out['month'] = out['month'].astype(int)

    # Cyclical semester features (period = 3 semesters).
    out['semester_sin'] = np.sin(2.0 * np.pi * out['semester'] / 3.0)
    out['semester_cos'] = np.cos(2.0 * np.pi * out['semester'] / 3.0)

    # Cyclical month features (period = 12 months).
    out['month_sin'] = np.sin(2.0 * np.pi * out['month'] / 12.0)
    out['month_cos'] = np.cos(2.0 * np.pi * out['month'] / 12.0)

    sort_cols = ['program_id', 'semester', 'year_start']
    for col in sort_cols:
        if col not in out.columns:
            raise ValueError(f"Missing required column: {col}")
    out = out.sort_values(sort_cols).reset_index(drop=True)

    # Same semester in the prior academic year for the same program.
    out['Same_Semester_Last_Year_Enrollment'] = (
        out.groupby(['program_id', 'semester'])[target_col]
        .shift(1)
    )

    return out


def build_xgboost_log_pipeline(random_state=42):
    """Builds StandardScaler + XGBoost with log1p/expm1 target transform."""
    reg_pipeline = Pipeline([
        ('scaler', StandardScaler()),
        ('xgb', XGBRegressor(
            n_estimators=300,
            max_depth=4,
            learning_rate=0.05,
            subsample=0.9,
            colsample_bytree=0.9,
            objective='reg:squarederror',
            random_state=random_state
        ))
    ])

    return TransformedTargetRegressor(
        regressor=reg_pipeline,
        func=np.log1p,
        inverse_func=np.expm1,
        check_inverse=False
    )


def prepare_lstm_supervised_data(df, feature_cols, target_col='total', seq_len=4):
    """Prepare standardized LSTM tensors with log1p-transformed target.

    Returns:
    - X_seq: shape (samples, seq_len, n_features)
    - y_seq_log: log1p target aligned with each sequence target step
    - X_scaler: fitted StandardScaler for feature transform reuse
    """
    work = df.dropna(subset=feature_cols + [target_col]).copy()
    if len(work) <= seq_len:
        raise ValueError("Not enough rows after lag/dropna to create LSTM sequences")

    X = work[feature_cols].astype(float).values
    y = work[target_col].astype(float).values

    X_scaler = StandardScaler()
    X_scaled = X_scaler.fit_transform(X)
    y_log = np.log1p(np.maximum(y, 0.0))

    X_seq = []
    y_seq_log = []
    for i in range(seq_len, len(work)):
        X_seq.append(X_scaled[i - seq_len:i, :])
        y_seq_log.append(y_log[i])

    return np.array(X_seq, dtype=float), np.array(y_seq_log, dtype=float), X_scaler


def build_feature_ready_dataset(df, target_col='total'):
    """Convenience wrapper that adds required features and returns ready feature list."""
    featured = add_enrollment_time_features(df, target_col=target_col)
    feature_cols = [
        'semester_sin',
        'semester_cos',
        'month_sin',
        'month_cos',
        'Same_Semester_Last_Year_Enrollment'
    ]
    ready = featured.dropna(subset=feature_cols + [target_col]).copy()
    return ready, feature_cols


def run_demo_case():
    # Replace with your own y_true/y_pred arrays if needed.
    y_true = np.array([120, 150, 180, 130, 160], dtype=float)
    y_pred = np.array([118, 147, 170, -10, 162], dtype=float)

    metrics = ModelEvaluator.calculate_metrics(y_true, y_pred)

    print("ModelEvaluator output:")
    for k, v in metrics.items():
        print(f"  {k}: {v}")

    explain_r2(metrics.get('Raw_R2'), metrics.get('Clipped_R2'))


if __name__ == '__main__':
    run_demo_case()
    check_seasonality_from_db()