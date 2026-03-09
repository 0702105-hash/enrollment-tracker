#!/usr/bin/env python3
"""
Multi-Model Enrollment Prediction System
Supports: SARMAX, Facebook Prophet, and LSTM
Generates predictions for multiple future years with comprehensive evaluation metrics
Saves all metrics to database for dashboard visualization
Also saves per-model predictions for dashboard chart comparison.
"""

import time
from datetime import datetime

import mysql.connector
import numpy as np
import pandas as pd
import warnings

warnings.filterwarnings('ignore')

from prophet import Prophet
from sklearn.metrics import mean_absolute_error, mean_squared_error, r2_score
from sklearn.preprocessing import MinMaxScaler
from statsmodels.tsa.statespace.sarimax import SARIMAX
from tensorflow.keras.callbacks import EarlyStopping
from tensorflow.keras.layers import LSTM, Dense, Dropout
from tensorflow.keras.models import Sequential
from tensorflow.keras.optimizers import Adam

print("=" * 80)
print("🔮 MULTI-MODEL ENROLLMENT PREDICTION SYSTEM (2026-2027+)")
print("Models: SARMAX | Facebook Prophet | LSTM")
print("=" * 80)

DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'casDB'
}

PROGRAM_NAMES = {
    1: 'BA Communication',
    2: 'BA English',
    3: 'BA PolSci',
    4: 'BLIS',
    5: 'BM Music',
    6: 'BS Biology',
    7: 'BSIT',
    8: 'BS Social Work'
}


class ModelEvaluator:
    """Calculate and store model evaluation metrics"""

    @staticmethod
    def calculate_metrics(y_true, y_pred, model_name=""):
        y_true = np.array(y_true, dtype=float)
        y_pred = np.array(y_pred, dtype=float)
        y_pred = np.maximum(y_pred, 0)

        mae = mean_absolute_error(y_true, y_pred)
        mse = mean_squared_error(y_true, y_pred)
        rmse = np.sqrt(mse)

        mask = y_true != 0
        if mask.any():
            mape = np.mean(np.abs((y_true[mask] - y_pred[mask]) / y_true[mask])) * 100
        else:
            mape = np.inf

        r2 = r2_score(y_true, y_pred)

        log_true = np.log1p(y_true)
        log_pred = np.log1p(y_pred)
        rmsle = np.sqrt(mean_squared_error(log_true, log_pred))

        if len(y_true) > 1:
            naive_pred = y_true[:-1]
            naive_rmse = np.sqrt(mean_squared_error(y_true[1:], naive_pred))
            theil_u = rmse / naive_rmse if naive_rmse != 0 else 0.0
        else:
            theil_u = 0.0

        return {
            'MAE': round(float(mae), 2),
            'RMSE': round(float(rmse), 2),
            'MAPE': round(float(mape), 2),
            'R²': round(float(r2), 4),
            'RMSLE': round(float(rmsle), 4),
            'Theil_U': round(float(theil_u), 4)
        }

    @staticmethod
    def print_metrics(metrics, model_name="Model"):
        print(f"\n   📊 {model_name} Evaluation Metrics:")
        print(f"      MAE:       {metrics['MAE']:<8}   (Mean Absolute Error)")
        print(f"      RMSE:      {metrics['RMSE']:<8}   (Root Mean Squared Error)")
        print(f"      MAPE:      {metrics['MAPE']:<8}%  (Mean Absolute Percentage Error)")
        print(f"      R²:        {metrics['R²']:<8}   (Coefficient of Determination)")
        print(f"      RMSLE:     {metrics['RMSLE']:<8}   (Root Mean Squared Log Error)")
        print(f"      Theil-U:   {metrics['Theil_U']:<8}   (Theil Inequality Coefficient)")


class SARMAXPredictor:
    """SARMAX (Seasonal ARIMA) for time series forecasting"""

    def __init__(self, order=(1, 1, 1), seasonal_order=(1, 1, 1, 3)):
        self.order = order
        self.seasonal_order = seasonal_order
        self.model = None
        self.fitted_model = None
        self.metrics = {}

    def train(self, y_train, y_test):
        print("      🔧 Training SARMAX model...")
        try:
            self.model = SARIMAX(
                y_train,
                order=self.order,
                seasonal_order=self.seasonal_order,
                enforce_stationarity=False,
                enforce_invertibility=False
            )
            self.fitted_model = self.model.fit(disp=False, maxiter=500)

            forecast = self.fitted_model.get_forecast(steps=len(y_test))
            predictions = np.array(forecast.predicted_mean)

            self.metrics = ModelEvaluator.calculate_metrics(y_test, predictions, "SARMAX")
            return True
        except Exception as e:
            print(f"      ❌ SARMAX training error: {str(e)}")
            return False

    def predict(self, steps=1):
        if self.fitted_model is None:
            return None

        try:
            forecast = self.fitted_model.get_forecast(steps=steps)
            predictions = np.array(forecast.predicted_mean)
            conf_int = forecast.conf_int()

            return {
                'predictions': np.maximum(predictions, 0),
                'lower_ci': np.maximum(np.array(conf_int.iloc[:, 0]), 0),
                'upper_ci': np.maximum(np.array(conf_int.iloc[:, 1]), 0),
                'metrics': self.metrics
            }
        except Exception as e:
            print(f"      ❌ SARMAX prediction error: {str(e)}")
            return None


class ProphetPredictor:
    """Facebook Prophet for time series forecasting with seasonality"""

    def __init__(self, yearly_seasonality=False, weekly_seasonality=False):
        self.yearly_seasonality = yearly_seasonality
        self.weekly_seasonality = weekly_seasonality
        self.model = None
        self.metrics = {}

    def prepare_data(self, values):
        return pd.DataFrame({
            'ds': pd.date_range(start='2015-01-01', periods=len(values), freq='3MS'),
            'y': values
        })

    def train(self, y_train, y_test):
        print("      🔧 Training Prophet model...")
        try:
            train_df = self.prepare_data(y_train)

            self.model = Prophet(
                yearly_seasonality=self.yearly_seasonality,
                weekly_seasonality=self.weekly_seasonality,
                interval_width=0.95,
                stan_backend='cmdstanpy'
            )
            self.model.fit(train_df)

            future = self.model.make_future_dataframe(periods=len(y_test), freq='3MS')
            forecast = self.model.predict(future)
            predictions = forecast['yhat'].tail(len(y_test)).values

            self.metrics = ModelEvaluator.calculate_metrics(y_test, predictions, "Prophet")
            return True
        except Exception as e:
            print(f"      ❌ Prophet training error: {str(e)}")
            return False

    def predict(self, steps=1):
        if self.model is None:
            return None

        try:
            future = self.model.make_future_dataframe(periods=steps, freq='3MS')
            forecast = self.model.predict(future)

            predictions = forecast['yhat'].tail(steps).values
            lower_ci = forecast['yhat_lower'].tail(steps).values
            upper_ci = forecast['yhat_upper'].tail(steps).values

            return {
                'predictions': np.maximum(predictions, 0),
                'lower_ci': np.maximum(lower_ci, 0),
                'upper_ci': np.maximum(upper_ci, 0),
                'metrics': self.metrics
            }
        except Exception as e:
            print(f"      ❌ Prophet prediction error: {str(e)}")
            return None


class LSTMPredictor:
    """LSTM (Long Short-Term Memory) Neural Network for time series"""

    def __init__(self, sequence_length=4, lstm_units=32, epochs=100, batch_size=8):
        self.sequence_length = sequence_length
        self.lstm_units = lstm_units
        self.epochs = epochs
        self.batch_size = batch_size
        self.model = None
        self.scaler = MinMaxScaler(feature_range=(0, 1))
        self.metrics = {}

    def create_sequences(self, data):
        X, y = [], []
        for i in range(len(data) - self.sequence_length):
            X.append(data[i:i + self.sequence_length])
            y.append(data[i + self.sequence_length])
        return np.array(X), np.array(y)

    def train(self, y_train, y_test):
        print("      🔧 Training LSTM model...")
        try:
            y_all = np.concatenate([y_train, y_test]).reshape(-1, 1)
            scaled_data = self.scaler.fit_transform(y_all)

            train_scaled = scaled_data[:len(y_train)]
            test_scaled = scaled_data[len(y_train):]

            X_train, y_train_seq = self.create_sequences(train_scaled)
            X_test, _ = self.create_sequences(test_scaled)

            if len(X_train) < 2 or len(X_test) < 2:
                print("      ⚠️  Insufficient data for LSTM")
                return False

            self.model = Sequential([
                LSTM(self.lstm_units, activation='relu', input_shape=(self.sequence_length, 1)),
                Dropout(0.2),
                Dense(16, activation='relu'),
                Dense(1)
            ])

            self.model.compile(optimizer=Adam(learning_rate=0.001), loss='mse')

            early_stop = EarlyStopping(
                monitor='val_loss',
                patience=10,
                restore_best_weights=True
            )

            self.model.fit(
                X_train, y_train_seq,
                epochs=self.epochs,
                batch_size=self.batch_size,
                validation_split=0.2,
                callbacks=[early_stop],
                verbose=0
            )

            predictions_scaled = self.model.predict(X_test, verbose=0)
            predictions = self.scaler.inverse_transform(predictions_scaled)
            y_test_actual = y_test[self.sequence_length:]

            self.metrics = ModelEvaluator.calculate_metrics(
                y_test_actual,
                predictions.flatten(),
                "LSTM"
            )
            return True
        except Exception as e:
            print(f"      ❌ LSTM training error: {str(e)}")
            return False

    def predict(self, y_hist, steps=1):
        if self.model is None:
            return None

        try:
            y_hist_all = np.array(y_hist).reshape(-1, 1)
            scaled_hist = self.scaler.transform(y_hist_all)

            current_seq = scaled_hist[-self.sequence_length:].reshape(1, self.sequence_length, 1)
            predictions = []

            for _ in range(steps):
                pred_scaled = self.model.predict(current_seq, verbose=0)[0, 0]
                predictions.append(pred_scaled)

                new_seq = np.append(current_seq[0, 1:, 0], pred_scaled)
                current_seq = new_seq.reshape(1, self.sequence_length, 1)

            predictions_array = np.array(predictions).reshape(-1, 1)
            predictions = self.scaler.inverse_transform(predictions_array)

            return {
                'predictions': np.maximum(predictions.flatten(), 0),
                'lower_ci': None,
                'upper_ci': None,
                'metrics': self.metrics
            }
        except Exception as e:
            print(f"      ❌ LSTM prediction error: {str(e)}")
            return None


def load_enrollment_data():
    """Load historical enrollment data from database"""
    print("\n📂 Loading historical data...")
    started = time.perf_counter()

    conn = mysql.connector.connect(**DB_CONFIG)
    try:
        df = pd.read_sql("""
            SELECT program_id, academic_year, semester, male, female,
                   (male + female) as total
            FROM enrollments
            WHERE academic_year NOT LIKE '%-2027'
            ORDER BY program_id, academic_year, semester
        """, conn)
    finally:
        conn.close()

    if df.empty:
        print("❌ No historical enrollment data found!")
        return None

    elapsed = time.perf_counter() - started
    print(f"✅ Loaded {len(df)} records from {df['program_id'].nunique()} programs in {elapsed:.2f}s")
    return df


def build_gender_ratio_map(df_hist):
    """Precompute male ratio per program once to avoid repeated DB queries."""
    ratio_map = {}

    grouped = df_hist.groupby('program_id')[['male', 'female']].sum().reset_index()
    for _, row in grouped.iterrows():
        total_male = float(row['male'])
        total_female = float(row['female'])
        total_all = total_male + total_female
        ratio_map[int(row['program_id'])] = (total_male / total_all) if total_all > 0 else 0.5

    return ratio_map


def get_model_confidence(pred_result):
    models_r2 = []

    if pred_result.get('sarmax') and pred_result['sarmax'].get('metrics'):
        models_r2.append(max(pred_result['sarmax']['metrics'].get('R²', 0), 0))
    if pred_result.get('prophet') and pred_result['prophet'].get('metrics'):
        models_r2.append(max(pred_result['prophet']['metrics'].get('R²', 0), 0))
    if pred_result.get('lstm') and pred_result['lstm'].get('metrics'):
        models_r2.append(max(pred_result['lstm']['metrics'].get('R²', 0), 0))

    return float(np.mean(models_r2)) if models_r2 else 0.5


def predict_for_program(program_id, program_data, future_years=1):
    print(f"\n{'=' * 60}")
    print(f"📚 Program {program_id}: {PROGRAM_NAMES.get(program_id, 'Unknown')}")
    print(f"{'=' * 60}")

    program_data = program_data.sort_values(['academic_year', 'semester']).reset_index(drop=True)

    if len(program_data) < 5:
        print(f"⚠️  Insufficient historical data ({len(program_data)} points)")
        return None

    y = program_data['total'].values.astype(float)

    split_idx = int(len(y) * 0.8)
    y_train = y[:split_idx]
    y_test = y[split_idx:]

    print("\n   📊 Data Summary:")
    print(f"      Total points: {len(y)} | Train: {len(y_train)} | Test: {len(y_test)}")
    print(f"      Min: {y.min():.0f} | Max: {y.max():.0f} | Mean: {y.mean():.0f}")

    steps = future_years * 3

    print("\n   🔮 MODEL 1: SARMAX (Seasonal ARIMA)")
    sarmax_started = time.perf_counter()
    sarmax_predictor = SARMAXPredictor()
    sarmax_success = sarmax_predictor.train(y_train, y_test)
    sarmax_pred = sarmax_predictor.predict(steps=steps) if sarmax_success else None
    if sarmax_pred and sarmax_pred.get('metrics'):
        ModelEvaluator.print_metrics(sarmax_pred['metrics'], "SARMAX")
    else:
        print("      ⚠️  SARMAX model could not be trained")
    print(f"      ⏱️ SARMAX time: {time.perf_counter() - sarmax_started:.2f}s")

    print("\n   🔮 MODEL 2: Facebook Prophet")
    prophet_started = time.perf_counter()
    prophet_predictor = ProphetPredictor()
    prophet_success = prophet_predictor.train(y_train, y_test)
    prophet_pred = prophet_predictor.predict(steps=steps) if prophet_success else None
    if prophet_pred and prophet_pred.get('metrics'):
        ModelEvaluator.print_metrics(prophet_pred['metrics'], "Prophet")
    else:
        print("      ⚠️  Prophet model could not be trained")
    print(f"      ⏱️ Prophet time: {time.perf_counter() - prophet_started:.2f}s")

    print("\n   🔮 MODEL 3: LSTM (Neural Network)")
    lstm_started = time.perf_counter()
    lstm_predictor = LSTMPredictor(sequence_length=max(1, min(4, len(y_train) // 2)))
    lstm_success = lstm_predictor.train(y_train, y_test)
    lstm_pred = lstm_predictor.predict(y, steps=steps) if lstm_success else None
    if lstm_pred and lstm_pred.get('metrics'):
        ModelEvaluator.print_metrics(lstm_pred['metrics'], "LSTM")
    else:
        print("      ⚠️  LSTM model could not be trained")
    print(f"      ⏱️ LSTM time: {time.perf_counter() - lstm_started:.2f}s")

    print("\n   📈 ENSEMBLE FORECAST (Average of successful models):")
    predictions_list = [
        sarmax_pred['predictions'] if sarmax_pred else None,
        prophet_pred['predictions'] if prophet_pred else None,
        lstm_pred['predictions'] if lstm_pred else None
    ]
    valid_predictions = [p for p in predictions_list if p is not None]

    if not valid_predictions:
        print("      ❌ No valid predictions from any model")
        return None

    ensemble_pred = np.mean(valid_predictions, axis=0)
    print(f"      ✅ Ensemble predictions: {len(valid_predictions)} models combined")

    return {
        'program_id': program_id,
        'program_name': PROGRAM_NAMES.get(program_id, f'Program {program_id}'),
        'sarmax': sarmax_pred,
        'prophet': prophet_pred,
        'lstm': lstm_pred,
        'ensemble': {
            'predictions': np.maximum(ensemble_pred, 0),
            'lower_ci': None,
            'upper_ci': None,
            'metrics': {'R²': get_model_confidence({
                'sarmax': sarmax_pred,
                'prophet': prophet_pred,
                'lstm': lstm_pred
            })}
        },
        'future_years': future_years,
        'historical_data': y
    }


def insert_prediction_rows(cursor, pred_result, future_years, avg_male_ratio):
    program_id = int(pred_result['program_id'])
    confidence = get_model_confidence(pred_result)
    base_year = 2026
    inserted_count = 0

    model_map = {
        'SARMAX': pred_result.get('sarmax'),
        'Prophet': pred_result.get('prophet'),
        'LSTM': pred_result.get('lstm'),
        'Ensemble': pred_result.get('ensemble')
    }

    for model_name, model_result in model_map.items():
        if not model_result or model_result.get('predictions') is None:
            continue

        predictions = model_result['predictions'][:future_years * 3]

        for sem_offset, pred_value in enumerate(predictions):
            sem = (sem_offset % 3) + 1
            year_offset = sem_offset // 3
            academic_year = f"{base_year + year_offset}-{base_year + year_offset + 1}"

            pred_total = int(max(float(pred_value), 0))
            pred_male = int(pred_total * avg_male_ratio)
            pred_female = pred_total - pred_male

            cursor.execute("""
                INSERT INTO predictions
                (program_id, academic_year, semester, predicted_total,
                 predicted_male, predicted_female, confidence, model_ensemble, model_name)
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
            """, (
                program_id,
                academic_year,
                int(sem),
                pred_total,
                pred_male,
                pred_female,
                confidence,
                'SARMAX+Prophet+LSTM',
                model_name
            ))
            inserted_count += 1

    return inserted_count


def save_predictions_to_db(all_predictions, future_years=1, gender_ratio_map=None):
    """Save per-model and ensemble predictions to database."""
    print(f"\n\n{'=' * 80}")
    print("💾 SAVING PREDICTIONS TO DATABASE")
    print(f"{'=' * 80}")

    gender_ratio_map = gender_ratio_map or {}
    started = time.perf_counter()

    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor()

    try:
        for year_offset in range(future_years):
            target_year = 2026 + year_offset
            cursor.execute(
                f"DELETE FROM predictions WHERE academic_year LIKE '{target_year}-%'"
            )

        conn.commit()
        print("✅ Cleared existing predictions")

        inserted_count = 0

        for pred_result in all_predictions:
            if pred_result is None:
                continue

            program_id = int(pred_result['program_id'])
            avg_male_ratio = gender_ratio_map.get(program_id, 0.5)

            inserted_count += insert_prediction_rows(
                cursor=cursor,
                pred_result=pred_result,
                future_years=future_years,
                avg_male_ratio=avg_male_ratio
            )

        conn.commit()
        print(f"✅ Saved {inserted_count} prediction rows to database in {time.perf_counter() - started:.2f}s")

    except Exception as e:
        print(f"❌ Database error: {str(e)}")
        conn.rollback()

    finally:
        cursor.close()
        conn.close()


def save_model_metrics_to_db(all_predictions):
    """Save individual model metrics to database for dashboard visualization"""
    print("\n💾 SAVING MODEL METRICS TO DATABASE")

    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor()

    try:
        cursor.execute("DELETE FROM model_metrics WHERE prediction_year >= '2026'")
        conn.commit()
        print("✅ Cleared existing metrics")

        inserted_count = 0

        for pred_result in all_predictions:
            if pred_result is None:
                continue

            program_id = pred_result['program_id']
            prediction_year = f"{2026}-{2026 + pred_result['future_years'] - 1}"

            for model_name, model_key in [('SARMAX', 'sarmax'), ('Prophet', 'prophet'), ('LSTM', 'lstm')]:
                model_result = pred_result.get(model_key)
                if model_result and model_result.get('metrics'):
                    for metric_name, metric_value in model_result['metrics'].items():
                        cursor.execute("""
                            INSERT INTO model_metrics
                            (program_id, model_name, metric_name, metric_value, prediction_year)
                            VALUES (%s, %s, %s, %s, %s)
                        """, (
                            int(program_id),
                            model_name,
                            str(metric_name),
                            float(metric_value),
                            prediction_year
                        ))
                        inserted_count += 1

        conn.commit()
        print(f"✅ Saved {inserted_count} metric records to database")

    except Exception as e:
        print(f"❌ Database error: {str(e)}")
        conn.rollback()

    finally:
        cursor.close()
        conn.close()


def print_model_performance_summary(all_predictions):
    print(f"\n\n{'=' * 80}")
    print("📊 MODEL PERFORMANCE SUMMARY")
    print(f"{'=' * 80}")

    print(f"\n✅ Processed {len(all_predictions)} programs")
    print("✅ Generated predictions with 3-model ensemble approach")

    print(f"\n{'=' * 80}")
    print("📈 MODEL COMPARISON")
    print(f"{'=' * 80}")

    print("\n1️⃣  SARMAX (Seasonal ARIMA)")
    print("   ├─ Type: Classical time series")
    print("   ├─ Order: (p=1, d=1, q=1)")
    print("   ├─ Seasonality: (P=1, D=1, Q=1, s=3)")
    print("   ├─ Pros: ✓ Fast, ✓ Interpretable, ✓ Confidence intervals")
    print("   └─ Cons: ✗ Assumes stationarity, ✗ Limited to linear patterns")

    print("\n2️⃣  Facebook Prophet")
    print("   ├─ Type: Trend + Seasonality decomposition")
    print("   ├─ Yearly Seasonality: Disabled")
    print("   ├─ Weekly Seasonality: Disabled")
    print("   ├─ Pros: ✓ Robust, ✓ Auto changepoint detection, ✓ Handles missing data")
    print("   └─ Cons: ✗ Slower training (Stan), ✗ May underestimate CI")

    print("\n3️⃣  LSTM (Deep Learning)")
    print("   ├─ Type: Recurrent Neural Network")
    print("   ├─ Architecture: 32 LSTM cells → 16 Dense → 1 Output")
    print("   ├─ Sequence Length: 4 timesteps")
    print("   ├─ Pros: ✓ Learns complex patterns, ✓ No stationarity assumption, ✓ Flexible")
    print("   └─ Cons: ✗ 'Black box', ✗ Needs more data (20+), ✗ Slower")

    print(f"\n{'=' * 80}")
    print("✨ ENSEMBLE APPROACH")
    print(f"{'=' * 80}")
    print("\nFormula: Ensemble = (SARMAX + Prophet + LSTM) / 3")
    print("\nBenefits:")
    print("  ✓ Reduces variance from individual model errors")
    print("  ✓ More robust to model-specific failures")
    print("  ✓ Captures strengths of all approaches")
    print("  ✓ Better generalization than any single model")


if __name__ == "__main__":
    total_started = time.perf_counter()

    df_hist = load_enrollment_data()
    if df_hist is None:
        exit(1)

    print("\n" + "=" * 80)
    print("🔮 MULTI-YEAR PREDICTION CONFIGURATION")
    print("=" * 80)

    future_years = 1
    print(f"\n📅 Predicting for {future_years} future year(s)")
    print(f"   Models will generate {future_years * 3} semester-level predictions")

    gender_ratio_map = build_gender_ratio_map(df_hist)

    all_predictions = []
    for program_id in sorted(df_hist['program_id'].unique()):
        program_data = df_hist[df_hist['program_id'] == program_id].copy()
        result = predict_for_program(program_id, program_data, future_years=future_years)
        all_predictions.append(result)

    save_predictions_to_db(all_predictions, future_years=future_years, gender_ratio_map=gender_ratio_map)
    save_model_metrics_to_db(all_predictions)
    print_model_performance_summary(all_predictions)

    print(f"\n\n{'=' * 80}")
    print(f"✨ PREDICTION COMPLETE - {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print(f"{'=' * 80}")

    successful = sum(1 for p in all_predictions if p is not None)
    print(f"\n📊 Results Summary:")
    print(f"   ✅ {successful}/{len(all_predictions)} programs processed successfully")
    print(f"   📈 Total prediction rows saved: {successful * future_years * 3 * 4}")
    print(f"   🤖 Model metrics saved: {successful * 3 * 6} (3 models × 6 metrics each)")
    print(f"   ⏱️ Total runtime: {time.perf_counter() - total_started:.2f}s")

    print(f"\n📱 Dashboard Access:")
    print("   🌐 http://localhost/enrollment-tracker/dashboard.php?login=1")
    print("   📌 Username: admin")
    print("   🔐 Password: admin123")

    print(f"\n{'=' * 80}\n")