#!/usr/bin/env python3
"""
Multi-Model Enrollment Prediction System
Supports: Facebook Prophet, LSTM, and XGBoost
Generates predictions for multiple future years with comprehensive evaluation metrics
Saves per-model predictions and metrics for dashboard visualization.
"""

import time
import warnings

import mysql.connector
import numpy as np
import pandas as pd

warnings.filterwarnings('ignore')

from prophet import Prophet
from sklearn.metrics import mean_absolute_error, mean_squared_error, r2_score
from sklearn.preprocessing import MinMaxScaler
from tensorflow.keras.callbacks import EarlyStopping
from tensorflow.keras.layers import LSTM, Dense, Dropout
from tensorflow.keras.models import Sequential
from tensorflow.keras.optimizers import Adam
from xgboost import XGBRegressor

print("=" * 80)
print("🔮 MULTI-MODEL ENROLLMENT PREDICTION SYSTEM (2026-2027+)")
print("Models: Facebook Prophet | LSTM | XGBoost")
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


def is_valid_metric_value(value):
    try:
        numeric_value = float(value)
        return np.isfinite(numeric_value)
    except (TypeError, ValueError):
        return False


class ModelEvaluator:
    @staticmethod
    def calculate_metrics(y_true, y_pred):
        y_true = np.array(y_true, dtype=float)
        y_pred = np.array(y_pred, dtype=float)
        y_pred = np.maximum(y_pred, 0)

        if len(y_true) == 0 or len(y_pred) == 0:
            return {
                'MAE': np.nan,
                'RMSE': np.nan,
                'MAPE': np.nan,
                'MSE': np.nan,
                'R²': np.nan,
                'RMSLE': np.nan,
                'Theil_U': np.nan
            }

        mae = mean_absolute_error(y_true, y_pred)
        mse = mean_squared_error(y_true, y_pred)
        rmse = np.sqrt(mse)

        mask = y_true != 0
        if mask.any():
            mape = np.mean(np.abs((y_true[mask] - y_pred[mask]) / y_true[mask])) * 100
        else:
            mape = np.nan

        try:
            r2 = r2_score(y_true, y_pred)
        except Exception:
            r2 = np.nan

        log_true = np.log1p(np.maximum(y_true, 0))
        log_pred = np.log1p(np.maximum(y_pred, 0))
        rmsle = np.sqrt(mean_squared_error(log_true, log_pred))

        if len(y_true) > 1:
            naive_pred = y_true[:-1]
            naive_rmse = np.sqrt(mean_squared_error(y_true[1:], naive_pred))
            theil_u = rmse / naive_rmse if naive_rmse != 0 else np.nan
        else:
            theil_u = np.nan

        return {
            'MAE': round(float(mae), 2) if is_valid_metric_value(mae) else np.nan,
            'RMSE': round(float(rmse), 2) if is_valid_metric_value(rmse) else np.nan,
            'MAPE': round(float(mape), 2) if is_valid_metric_value(mape) else np.nan,
            'MSE': round(float(mse), 2) if is_valid_metric_value(mse) else np.nan,
            'R²': round(float(r2), 4) if is_valid_metric_value(r2) else np.nan,
            'RMSLE': round(float(rmsle), 4) if is_valid_metric_value(rmsle) else np.nan,
            'Theil_U': round(float(theil_u), 4) if is_valid_metric_value(theil_u) else np.nan
        }

    @staticmethod
    def print_metrics(metrics, model_name="Model"):
        print(f"\n   📊 {model_name} Evaluation Metrics:")
        for key, value in metrics.items():
            print(f"      {key:<16}: {value}")


class ProphetPredictor:
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
                stan_backend='CMDSTANPY'
            )
            self.model.fit(train_df)

            future = self.model.make_future_dataframe(periods=len(y_test), freq='3MS')
            forecast = self.model.predict(future)
            predictions = forecast['yhat'].tail(len(y_test)).values

            self.metrics = ModelEvaluator.calculate_metrics(y_test, predictions)

            mask = np.array(y_test) != 0
            if mask.any():
                mdape = np.median(
                    np.abs((np.array(y_test)[mask] - predictions[mask]) / np.array(y_test)[mask])
                ) * 100
                self.metrics['MdAPE'] = round(float(mdape), 2) if is_valid_metric_value(mdape) else np.nan
            else:
                self.metrics['MdAPE'] = np.nan

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
    def __init__(self, sequence_length=3, lstm_units=32, epochs=50, batch_size=4):
        self.sequence_length = sequence_length
        self.lstm_units = lstm_units
        self.epochs = epochs
        self.batch_size = batch_size
        self.model = None
        self.scaler = MinMaxScaler(feature_range=(0, 1))
        self.metrics = {}

    def create_sequences(self, data):
        X, y, target_indices = [], [], []
        for i in range(len(data) - self.sequence_length):
            X.append(data[i:i + self.sequence_length])
            y.append(data[i + self.sequence_length])
            target_indices.append(i + self.sequence_length)
        return np.array(X), np.array(y), np.array(target_indices)

    def train(self, y_train, y_test):
        print("      🔧 Training LSTM model...")
        try:
            y_train_arr = np.array(y_train, dtype=float).reshape(-1, 1)
            y_test_arr = np.array(y_test, dtype=float).reshape(-1, 1)
            y_all = np.concatenate([y_train_arr, y_test_arr], axis=0)

            # Fit scaler on training history only to avoid look-ahead leakage.
            self.scaler.fit(y_train_arr)
            scaled_all = self.scaler.transform(y_all)

            X_all, y_all_seq, target_indices = self.create_sequences(scaled_all)
            if len(X_all) < 1:
                print("      ⚠️  Insufficient data for LSTM")
                return False

            split_point = len(y_train_arr)
            train_mask = target_indices < split_point
            test_mask = target_indices >= split_point

            X_train = X_all[train_mask]
            y_train_seq = y_all_seq[train_mask]
            X_test = X_all[test_mask]
            y_test_actual = y_all.flatten()[target_indices[test_mask]]

            if len(X_train) < 2 or len(X_test) < 1:
                print("      ⚠️  Insufficient data for LSTM")
                return False

            self.model = Sequential([
                LSTM(self.lstm_units, input_shape=(self.sequence_length, 1), recurrent_dropout=0.1),
                Dropout(0.1),
                Dense(16, activation='relu'),
                Dense(1)
            ])

            self.model.compile(optimizer=Adam(learning_rate=0.001), loss='mse')

            callbacks = []
            if len(X_train) > 3:
                early_stop = EarlyStopping(
                    monitor='val_loss',
                    patience=8,
                    restore_best_weights=True
                )
                callbacks.append(early_stop)

            fit_kwargs = {
                'x': X_train,
                'y': y_train_seq,
                'epochs': self.epochs,
                'batch_size': self.batch_size,
                'callbacks': callbacks,
                'verbose': 0
            }

            if len(X_train) > 3:
                fit_kwargs['validation_split'] = 0.2

            history = self.model.fit(**fit_kwargs)

            predictions_scaled = self.model.predict(X_test, verbose=0)
            predictions = self.scaler.inverse_transform(predictions_scaled)

            self.metrics = ModelEvaluator.calculate_metrics(y_test_actual, predictions.flatten())

            train_loss = history.history['loss'][-1] if history.history.get('loss') else np.nan
            val_loss = history.history['val_loss'][-1] if history.history.get('val_loss') else np.nan

            self.metrics['Training_Loss'] = round(float(train_loss), 4) if is_valid_metric_value(train_loss) else np.nan
            self.metrics['Validation_Loss'] = round(float(val_loss), 4) if is_valid_metric_value(val_loss) else np.nan

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

            if len(scaled_hist) < self.sequence_length:
                print("      ⚠️  Not enough history for LSTM prediction")
                return None

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


class XGBoostPredictor:
    def __init__(self, n_lags=3):
        self.n_lags = n_lags
        self.model = None
        self.metrics = {}
        self.history_values = None

    def create_supervised(self, values):
        X, y = [], []
        for i in range(self.n_lags, len(values)):
            X.append(values[i - self.n_lags:i])
            y.append(values[i])
        return np.array(X), np.array(y)

    def train(self, y_train, y_test):
        print("      🔧 Training XGBoost model...")
        try:
            full_series = np.concatenate([y_train, y_test]).astype(float)
            effective_lags = min(self.n_lags, max(1, len(y_train) - 1))
            self.n_lags = effective_lags

            rolling_test_predictions = []
            rolling_history = list(y_train.astype(float))

            for actual in y_test:
                X_roll, y_roll = self.create_supervised(np.array(rolling_history, dtype=float))
                if len(X_roll) < 1:
                    print("      ⚠️  Insufficient data for XGBoost")
                    return False

                model = XGBRegressor(
                    n_estimators=100,
                    max_depth=3,
                    learning_rate=0.05,
                    subsample=0.9,
                    colsample_bytree=0.9,
                    objective='reg:squarederror',
                    random_state=42
                )
                model.fit(X_roll, y_roll)

                x_input = np.array(rolling_history[-self.n_lags:], dtype=float).reshape(1, -1)
                pred = model.predict(x_input)[0]
                rolling_test_predictions.append(pred)
                rolling_history.append(float(actual))

            self.metrics = ModelEvaluator.calculate_metrics(y_test, np.array(rolling_test_predictions))
            self.history_values = full_series.copy()

            self.model = XGBRegressor(
                n_estimators=100,
                max_depth=3,
                learning_rate=0.05,
                subsample=0.9,
                colsample_bytree=0.9,
                objective='reg:squarederror',
                random_state=42
            )

            X_full, y_full = self.create_supervised(full_series)
            if len(X_full) < 1:
                print("      ⚠️  Insufficient data for final XGBoost fit")
                return False

            self.model.fit(X_full, y_full)
            return True

        except Exception as e:
            print(f"      ❌ XGBoost training error: {str(e)}")
            return False

    def predict(self, y_hist, steps=1):
        if self.model is None:
            return None

        try:
            history = list(np.array(y_hist, dtype=float))
            if len(history) < self.n_lags:
                return None

            predictions = []
            for _ in range(steps):
                x_input = np.array(history[-self.n_lags:], dtype=float).reshape(1, -1)
                pred = self.model.predict(x_input)[0]
                pred = max(float(pred), 0)
                predictions.append(pred)
                history.append(pred)

            return {
                'predictions': np.array(predictions, dtype=float),
                'lower_ci': None,
                'upper_ci': None,
                'metrics': self.metrics
            }
        except Exception as e:
            print(f"      ❌ XGBoost prediction error: {str(e)}")
            return None


def load_enrollment_data():
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

    print(f"✅ Loaded {len(df)} records from {df['program_id'].nunique()} programs in {time.perf_counter() - started:.2f}s")
    return df


def build_gender_ratio_map(df_hist):
    ratio_map = {}
    grouped = df_hist.groupby('program_id')[['male', 'female']].sum().reset_index()

    for _, row in grouped.iterrows():
        total_male = float(row['male'])
        total_female = float(row['female'])
        total_all = total_male + total_female
        ratio_map[int(row['program_id'])] = (total_male / total_all) if total_all > 0 else 0.5

    return ratio_map


def clip_value(value, min_value=0.0, max_value=1.0):
    return float(np.clip(float(value), float(min_value), float(max_value)))


def score_r2(r2_value):
    if not is_valid_metric_value(r2_value):
        return None

    # Normalize R² from [-1, 1] to [0, 1] while clipping outliers.
    clipped_r2 = clip_value(r2_value, -1.0, 1.0)
    return (clipped_r2 + 1.0) / 2.0


def score_mape(mape_value):
    if not is_valid_metric_value(mape_value):
        return None

    mape = max(float(mape_value), 0.0)
    if mape <= 5:
        return 1.0
    if mape <= 10:
        return 0.9
    if mape <= 20:
        return 0.75
    if mape <= 30:
        return 0.6
    if mape <= 50:
        return 0.4
    if mape <= 100:
        return 0.2
    return 0.05


def score_theil_u(theil_u_value):
    if not is_valid_metric_value(theil_u_value):
        return None

    theil_u = max(float(theil_u_value), 0.0)
    if theil_u <= 0.5:
        return 1.0
    if theil_u <= 1.0:
        return 0.8
    if theil_u <= 1.5:
        return 0.6
    if theil_u <= 2.0:
        return 0.4
    if theil_u <= 3.0:
        return 0.25
    return 0.1


def get_model_quality_score(model_result):
    if not model_result or not model_result.get('metrics'):
        return 0.5

    metrics = model_result['metrics']
    weighted_scores = []
    total_weight = 0.0

    metric_components = [
        ('R²', 0.45, score_r2),
        ('MAPE', 0.35, score_mape),
        ('Theil_U', 0.20, score_theil_u)
    ]

    for metric_name, weight, scorer in metric_components:
        score = scorer(metrics.get(metric_name))
        if score is None:
            continue
        weighted_scores.append(score * weight)
        total_weight += weight

    if total_weight == 0:
        return 0.5

    quality_score = sum(weighted_scores) / total_weight
    return clip_value(quality_score, 0.05, 0.98)


def get_prediction_stability(pred_result):
    first_step_predictions = []

    for key in ['prophet', 'lstm', 'xgboost']:
        model_result = pred_result.get(key)
        if not model_result or model_result.get('predictions') is None:
            continue

        preds = np.array(model_result['predictions'], dtype=float)
        if len(preds) > 0 and np.isfinite(preds[0]):
            first_step_predictions.append(float(preds[0]))

    if len(first_step_predictions) < 2:
        return 0.85

    preds = np.array(first_step_predictions, dtype=float)
    spread = np.std(preds)
    scale = max(np.mean(np.abs(preds)), 1.0)
    coeff_var = spread / scale

    # Lower disagreement across models increases confidence.
    stability = 1.0 / (1.0 + 2.0 * coeff_var)
    return clip_value(stability, 0.35, 1.0)


def get_model_confidence(pred_result):
    quality_scores = []

    for key in ['prophet', 'lstm', 'xgboost']:
        model_result = pred_result.get(key)
        if model_result and model_result.get('metrics'):
            quality_scores.append(get_model_quality_score(model_result))

    if not quality_scores:
        return 0.5

    base_quality = float(np.mean(quality_scores))
    stability = get_prediction_stability(pred_result)

    # Blend quality (validation metrics) with cross-model agreement.
    confidence = base_quality * (0.65 + 0.35 * stability)
    return clip_value(confidence, 0.05, 0.98)


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

    print("\n   🔮 MODEL 1: Facebook Prophet")
    prophet_predictor = ProphetPredictor()
    prophet_success = prophet_predictor.train(y_train, y_test)
    prophet_pred = prophet_predictor.predict(steps=steps) if prophet_success else None
    if prophet_pred and prophet_pred.get('metrics'):
        ModelEvaluator.print_metrics(prophet_pred['metrics'], "Prophet")

    print("\n   🔮 MODEL 2: LSTM (Neural Network)")
    lstm_seq_len = min(4, max(2, len(y_train) // 5 if len(y_train) >= 5 else 2))
    lstm_predictor = LSTMPredictor(sequence_length=lstm_seq_len, epochs=80, batch_size=4)
    lstm_success = lstm_predictor.train(y_train, y_test)
    lstm_pred = lstm_predictor.predict(y, steps=steps) if lstm_success else None
    if lstm_pred and lstm_pred.get('metrics'):
        ModelEvaluator.print_metrics(lstm_pred['metrics'], "LSTM")

    print("\n   🔮 MODEL 3: XGBoost")
    xgb_predictor = XGBoostPredictor(n_lags=min(3, max(1, len(y_train) - 1)))
    xgb_success = xgb_predictor.train(y_train, y_test)
    xgb_pred = xgb_predictor.predict(y, steps=steps) if xgb_success else None
    if xgb_pred and xgb_pred.get('metrics'):
        ModelEvaluator.print_metrics(xgb_pred['metrics'], "XGBoost")

    model_outputs = [
        ('prophet', prophet_pred),
        ('lstm', lstm_pred),
        ('xgboost', xgb_pred)
    ]

    weighted_predictions = []
    model_weights = []
    valid_models = []

    for model_key, model_result in model_outputs:
        if not model_result or model_result.get('predictions') is None:
            continue

        weight = get_model_quality_score(model_result)
        weighted_predictions.append(np.array(model_result['predictions'], dtype=float))
        model_weights.append(weight)
        valid_models.append(model_key)

    valid_predictions = weighted_predictions

    if not valid_predictions:
        print("      ❌ No valid predictions from any model")
        return None

    if len(valid_predictions) == 1:
        ensemble_pred = valid_predictions[0]
    else:
        stacked_predictions = np.vstack(valid_predictions)
        ensemble_pred = np.average(stacked_predictions, axis=0, weights=np.array(model_weights, dtype=float))

    weights_text = ", ".join(
        f"{model_name}={weight:.2f}" for model_name, weight in zip(valid_models, model_weights)
    )
    print(f"   ⚖️ Ensemble weights: {weights_text}")

    return {
        'program_id': program_id,
        'program_name': PROGRAM_NAMES.get(program_id, f'Program {program_id}'),
        'prophet': prophet_pred,
        'lstm': lstm_pred,
        'xgboost': xgb_pred,
        'ensemble': {
            'predictions': np.maximum(ensemble_pred, 0),
            'lower_ci': None,
            'upper_ci': None,
            'metrics': {
                'R²': get_model_confidence({
                    'prophet': prophet_pred,
                    'lstm': lstm_pred,
                    'xgboost': xgb_pred
                })
            }
        },
        'future_years': future_years
    }


def get_table_columns(cursor, table_name):
    cursor.execute("""
        SELECT COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = %s
    """, (table_name,))
    return {row[0] for row in cursor.fetchall()}


def ensure_predictions_schema(cursor):
    columns = get_table_columns(cursor, 'predictions')

    if 'model_ensemble' not in columns:
        cursor.execute("""
            ALTER TABLE predictions
            ADD COLUMN model_ensemble VARCHAR(255) DEFAULT 'Prophet+LSTM+XGBoost'
        """)

    if 'model_name' not in columns:
        cursor.execute("""
            ALTER TABLE predictions
            ADD COLUMN model_name VARCHAR(50) DEFAULT 'Ensemble'
        """)

    cursor.execute("SHOW INDEX FROM predictions")
    index_rows = cursor.fetchall()

    index_map = {}
    for row in index_rows:
        key_name = row[2]
        non_unique = row[1]
        seq_in_index = int(row[3])
        column_name = row[4]

        if key_name not in index_map:
            index_map[key_name] = {
                'non_unique': non_unique,
                'columns': {}
            }

        index_map[key_name]['columns'][seq_in_index] = column_name

    for key_name, meta in index_map.items():
        if meta['non_unique'] != 0:
            continue

        ordered_cols = [meta['columns'][k] for k in sorted(meta['columns'])]
        if ordered_cols == ['program_id', 'academic_year', 'semester']:
            cursor.execute(f"ALTER TABLE predictions DROP INDEX {key_name}")

    has_target_unique = False
    for meta in index_map.values():
        if meta['non_unique'] != 0:
            continue
        ordered_cols = [meta['columns'][k] for k in sorted(meta['columns'])]
        if ordered_cols == ['program_id', 'academic_year', 'semester', 'model_name']:
            has_target_unique = True
            break

    if not has_target_unique:
        cursor.execute("""
            ALTER TABLE predictions
            ADD UNIQUE KEY uk_program_pred_sem_model (program_id, academic_year, semester, model_name)
        """)


def insert_prediction_rows(cursor, pred_result, future_years, avg_male_ratio, predictions_columns):
    program_id = int(pred_result['program_id'])
    program_confidence = get_model_confidence(pred_result)
    base_year = 2026
    inserted_count = 0
    has_model_name = 'model_name' in predictions_columns
    has_model_ensemble = 'model_ensemble' in predictions_columns

    model_map = {
        'Prophet': pred_result.get('prophet'),
        'LSTM': pred_result.get('lstm'),
        'XGBoost': pred_result.get('xgboost'),
        'Ensemble': pred_result.get('ensemble')
    }

    # Older schemas don't include model_name, so store only ensemble rows to avoid insert errors.
    if not has_model_name:
        model_map = {'Ensemble': pred_result.get('ensemble')}

    for model_name, model_result in model_map.items():
        if not model_result or model_result.get('predictions') is None:
            continue

        model_quality = get_model_quality_score(model_result)
        if model_name == 'Ensemble':
            row_confidence = program_confidence
        else:
            row_confidence = clip_value((0.7 * model_quality) + (0.3 * program_confidence), 0.05, 0.98)

        predictions = model_result['predictions'][:future_years * 3]

        for sem_offset, pred_value in enumerate(predictions):
            sem = (sem_offset % 3) + 1
            year_offset = sem_offset // 3
            academic_year = f"{base_year + year_offset}-{base_year + year_offset + 1}"

            pred_total = int(max(float(pred_value), 0))
            pred_male = int(pred_total * avg_male_ratio)
            pred_female = pred_total - pred_male

            columns = [
                'program_id',
                'academic_year',
                'semester',
                'predicted_total',
                'predicted_male',
                'predicted_female',
                'confidence'
            ]
            values = [
                program_id,
                academic_year,
                int(sem),
                pred_total,
                pred_male,
                pred_female,
                row_confidence
            ]

            if has_model_ensemble:
                columns.append('model_ensemble')
                values.append('Prophet+LSTM+XGBoost')

            if has_model_name:
                columns.append('model_name')
                values.append(model_name)

            placeholders = ', '.join(['%s'] * len(values))
            columns_sql = ', '.join(columns)

            cursor.execute(
                f"INSERT INTO predictions ({columns_sql}) VALUES ({placeholders})",
                tuple(values)
            )
            inserted_count += 1

    return inserted_count


def save_predictions_to_db(all_predictions, future_years=1, gender_ratio_map=None):
    print(f"\n\n{'=' * 80}")
    print("💾 SAVING PREDICTIONS TO DATABASE")
    print(f"{'=' * 80}")

    gender_ratio_map = gender_ratio_map or {}

    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor()

    try:
        ensure_predictions_schema(cursor)
        predictions_columns = get_table_columns(cursor, 'predictions')

        for year_offset in range(future_years):
            target_year = 2026 + year_offset
            cursor.execute(f"DELETE FROM predictions WHERE academic_year LIKE '{target_year}-%'")

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
                avg_male_ratio=avg_male_ratio,
                predictions_columns=predictions_columns
            )

        conn.commit()
        print(f"✅ Saved {inserted_count} prediction rows to database")

    except Exception as e:
        print(f"❌ Database error: {str(e)}")
        conn.rollback()

    finally:
        cursor.close()
        conn.close()


def save_model_metrics_to_db(all_predictions):
    print("\n💾 SAVING MODEL METRICS TO DATABASE")

    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor()

    try:
        cursor.execute("DELETE FROM model_metrics WHERE prediction_year >= '2026'")
        conn.commit()
        print("✅ Cleared existing metrics")

        inserted_count = 0
        skipped_count = 0

        for pred_result in all_predictions:
            if pred_result is None:
                continue

            program_id = pred_result['program_id']
            prediction_year = f"{2026}-{2026 + pred_result['future_years'] - 1}"

            for model_name, model_key in [
                ('Prophet', 'prophet'),
                ('LSTM', 'lstm'),
                ('XGBoost', 'xgboost')
            ]:
                model_result = pred_result.get(model_key)
                if model_result and model_result.get('metrics'):
                    for metric_name, metric_value in model_result['metrics'].items():
                        if not is_valid_metric_value(metric_value):
                            skipped_count += 1
                            print(f"⚠️ Skipping invalid metric: {model_name} | {metric_name} = {metric_value}")
                            continue

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
        if skipped_count:
            print(f"⚠️ Skipped {skipped_count} invalid metric values")

    except Exception as e:
        print(f"❌ Database error: {str(e)}")
        conn.rollback()

    finally:
        cursor.close()
        conn.close()


if __name__ == "__main__":
    total_started = time.perf_counter()

    df_hist = load_enrollment_data()
    if df_hist is None:
        exit(1)

    future_years = 1
    gender_ratio_map = build_gender_ratio_map(df_hist)

    all_predictions = []
    for program_id in sorted(df_hist['program_id'].unique()):
        program_data = df_hist[df_hist['program_id'] == program_id].copy()
        result = predict_for_program(program_id, program_data, future_years=future_years)
        all_predictions.append(result)

    save_predictions_to_db(all_predictions, future_years=future_years, gender_ratio_map=gender_ratio_map)
    save_model_metrics_to_db(all_predictions)

    successful = sum(1 for p in all_predictions if p is not None)
    print(f"\n✅ {successful}/{len(all_predictions)} programs processed successfully")
    print(f"⏱️ Total runtime: {time.perf_counter() - total_started:.2f}s")