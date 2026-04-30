#!/usr/bin/env python3
"""
SARMAX + LSTM Enrollment Prediction System
Skipping Prophet due to installation issues
"""

import os
import sys

os.environ.setdefault('TF_CPP_MIN_LOG_LEVEL', '2')
os.environ.setdefault('TF_ENABLE_ONEDNN_OPTS', '0')

import pandas as pd
import numpy as np
import mysql.connector
from datetime import datetime
import warnings
warnings.filterwarnings('ignore')

if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8', errors='replace')
if hasattr(sys.stderr, 'reconfigure'):
    sys.stderr.reconfigure(encoding='utf-8', errors='replace')

from sklearn.metrics import (
    mean_absolute_error,
    mean_squared_error,
    r2_score
)

from statsmodels.tsa.statespace.sarimax import SARIMAX
from statsmodels.stats.diagnostic import acorr_ljungbox

try:
    import tensorflow as tf
    from tensorflow.keras.models import Sequential
    from tensorflow.keras.layers import LSTM, Dense, Dropout
    from tensorflow.keras.optimizers import Adam
    from tensorflow.keras.callbacks import EarlyStopping
    from sklearn.preprocessing import StandardScaler
    LSTM_AVAILABLE = True
    tf.get_logger().setLevel('ERROR')
except:
    LSTM_AVAILABLE = False

print("=" * 100)
print("🔮 TWO-MODEL ENROLLMENT PREDICTION SYSTEM")
print("=" * 100)
print("Models: SARMAX | LSTM")
print("=" * 100)

DB_CONFIG = {
    'host': 'localhost', 
    'user': 'root', 
    'password': '', 
    'database': 'casDB'
}

PROGRAM_NAMES = {
    1: 'BA Communication', 
    2: 'BA English', 
    3: 'BA Political Science', 
    4: 'BLIS',
    5: 'BM Music Education', 
    6: 'BS Biology', 
    7: 'BS Information Technology', 
    8: 'BS Social Work'
}

CONFIDENCE_MIN = 0.05
CONFIDENCE_MAX = 0.98

SEMESTER_MONTH_MAP = {
    1: 8,
    2: 1,
    3: 6
}


def is_valid_db_number(value):
    try:
        return np.isfinite(float(value))
    except (TypeError, ValueError):
        return False


def get_month_for_semester(semester):
    return int(SEMESTER_MONTH_MAP.get(int(semester), 1))


def make_temporal_feature_row(lag1_total, same_sem_last_year, semester, trend_index):
    month = get_month_for_semester(semester)
    sem_angle = 2.0 * np.pi * (float(semester) - 1.0) / 3.0
    month_angle = 2.0 * np.pi * (float(month) - 1.0) / 12.0

    return np.array([
        float(lag1_total),
        float(same_sem_last_year),
        float(np.sin(sem_angle)),
        float(np.cos(sem_angle)),
        float(np.sin(month_angle)),
        float(np.cos(month_angle)),
        float(trend_index)
    ], dtype=float)


def build_temporal_supervised(totals, semesters):
    totals = np.array(totals, dtype=float)
    semesters = np.array(semesters, dtype=int)

    if len(totals) != len(semesters):
        raise ValueError("Totals and semesters length mismatch")

    if len(totals) < 2:
        return np.array([]), np.array([]), np.array([])

    X_rows = []
    y_values = []
    target_indices = []

    last_seen_by_sem = {int(semesters[0]): float(totals[0])}

    for t in range(1, len(totals)):
        sem = int(semesters[t])
        lag1_total = float(totals[t - 1])
        same_sem_last_year = float(last_seen_by_sem.get(sem, lag1_total))

        X_rows.append(make_temporal_feature_row(
            lag1_total=lag1_total,
            same_sem_last_year=same_sem_last_year,
            semester=sem,
            trend_index=t
        ))
        y_values.append(float(totals[t]))
        target_indices.append(t)

        last_seen_by_sem[sem] = float(totals[t])

    return np.array(X_rows, dtype=float), np.array(y_values, dtype=float), np.array(target_indices, dtype=int)


def build_last_seen_semester_map(semesters, totals):
    mapping = {}
    for sem, total in zip(np.array(semesters, dtype=int), np.array(totals, dtype=float)):
        mapping[int(sem)] = float(total)
    return mapping


def get_next_semester(current_semester):
    return int((int(current_semester) % 3) + 1)


def clip_value(value, min_value=0.0, max_value=1.0):
    return float(np.clip(float(value), float(min_value), float(max_value)))


def score_r2(r2_value):
    if not is_valid_db_number(r2_value):
        return None

    # Normalize R² from [-1, 1] into [0, 1] for confidence scoring.
    clipped_r2 = clip_value(r2_value, -1.0, 1.0)
    return (clipped_r2 + 1.0) / 2.0


def score_mape(mape_value):
    if not is_valid_db_number(mape_value):
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


def get_model_quality_from_metrics(metrics):
    if not metrics:
        return 0.5

    weighted_scores = []
    total_weight = 0.0

    metric_components = [
        ('R²', 0.55, score_r2),
        ('MAPE', 0.45, score_mape)
    ]

    for metric_name, weight, scorer in metric_components:
        score = scorer(metrics.get(metric_name))
        if score is None:
            continue
        weighted_scores.append(score * weight)
        total_weight += weight

    if total_weight == 0:
        return 0.5

    return clip_value(sum(weighted_scores) / total_weight, CONFIDENCE_MIN, CONFIDENCE_MAX)


def get_prediction_stability(prediction_arrays):
    valid = [np.array(p, dtype=float) for p in prediction_arrays if p is not None and len(p) > 0]
    if len(valid) < 2:
        return 0.85

    # Evaluate agreement across the full forecast horizon, not just the first step.
    matrix = np.vstack(valid)
    per_step_mean = np.maximum(np.mean(np.abs(matrix), axis=0), 1.0)
    per_step_cv = np.std(matrix, axis=0) / per_step_mean
    avg_cv = float(np.mean(per_step_cv))

    stability = 1.0 / (1.0 + 2.0 * avg_cv)
    return clip_value(stability, 0.35, 1.0)


def get_ensemble_confidence(sarmax_pred, lstm_pred):
    model_results = [p for p in [sarmax_pred, lstm_pred] if p is not None]
    if not model_results:
        return 0.5

    quality_scores = [get_model_quality_from_metrics(model.get('metrics', {})) for model in model_results]
    base_quality = float(np.mean(quality_scores)) if quality_scores else 0.5

    stability = get_prediction_stability([model.get('predictions') for model in model_results])

    confidence = 0.15 + (base_quality * (0.65 + 0.35 * stability))
    return clip_value(confidence, CONFIDENCE_MIN, CONFIDENCE_MAX)


class SARMAXPredictor:
    """SARMAX Model"""
    
    def __init__(self):
        self.fitted_model = None
        self.metrics = {}
    
    def train(self, y_train, y_test):
        """Train SARMAX"""
        print(f"\n   🔧 Training SARMAX...")
        
        try:
            y_train_series = pd.Series(y_train.astype(float))
            
            model = SARIMAX(
                y_train_series,
                order=(1,1,1),
                seasonal_order=(1,1,1,3),
                enforce_stationarity=False,
                enforce_invertibility=False
            )
            self.fitted_model = model.fit(disp=False, maxiter=500)
            
            forecast = self.fitted_model.get_forecast(steps=len(y_test))
            predictions = forecast.predicted_mean.values
            
            mae = float(mean_absolute_error(y_test, predictions))
            mse = float(mean_squared_error(y_test, predictions))
            rmse = float(np.sqrt(mse))
            
            mask = y_test != 0
            if mask.any():
                mape = float(np.mean(np.abs((y_test[mask] - predictions[mask]) / y_test[mask])) * 100)
            else:
                mape = 0.0

            if np.var(y_test) == 0:
                r2 = np.nan
            else:
                r2 = float(r2_score(y_test, predictions))
            
            aic = float(self.fitted_model.aic)
            bic = float(self.fitted_model.bic)
            
            residuals = y_train_series - self.fitted_model.fittedvalues
            lb_test = acorr_ljungbox(residuals, lags=10, return_df=True)
            ljung_box_pvalue = float(lb_test.iloc[-1, 1])
            
            self.metrics = {
                'MAE': round(mae, 2),
                'RMSE': round(rmse, 2),
                'MAPE': round(mape, 2),
                'MSE': round(mse, 2),
                'R²': round(r2, 4) if is_valid_db_number(r2) else np.nan,
                'AIC': round(aic, 2),
                'BIC': round(bic, 2),
                'Ljung_Box_Pvalue': round(ljung_box_pvalue, 4)
            }
            
            print(f"      ✅ SARMAX training successful")
            return True
            
        except Exception as e:
            print(f"      ❌ SARMAX error: {str(e)}")
            return False
    
    def predict(self, y_hist, steps=3):
        """Generate predictions"""
        if self.fitted_model is None:
            return None
        
        try:
            forecast = self.fitted_model.get_forecast(steps=steps)
            predictions = forecast.predicted_mean.values
            
            return {
                'model': 'SARMAX',
                'predictions': np.maximum(predictions.astype(float), 0),
                'metrics': self.metrics
            }
        except Exception as e:
            print(f"      ❌ Prediction error: {str(e)}")
            return None


class LSTMPredictor:
    """LSTM Model"""
    
    def __init__(self, sequence_length=4):
        self.sequence_length = sequence_length
        self.model = None
        self.feature_scaler = StandardScaler()
        self.metrics = {}
        self.last_semester = None
        self.last_trend_index = None
        self.semester_last_value_map = {}
    
    def create_sequences(self, X_data, y_data):
        """Create sequences"""
        X, y, target_positions = [], [], []
        for i in range(len(X_data) - self.sequence_length + 1):
            target_pos = i + self.sequence_length - 1
            X.append(X_data[i:i + self.sequence_length])
            y.append(y_data[target_pos])
            target_positions.append(target_pos)
        return np.array(X), np.array(y), np.array(target_positions)
    
    def train(self, y_train, y_test, sem_train, sem_test):
        """Train LSTM"""
        print(f"\n   🔧 Training LSTM...")
        
        if not LSTM_AVAILABLE:
            print(f"      ⚠️  LSTM not available")
            return False
        
        try:
            y_train_arr = np.array(y_train, dtype=float)
            y_test_arr = np.array(y_test, dtype=float)
            sem_train_arr = np.array(sem_train, dtype=int)
            sem_test_arr = np.array(sem_test, dtype=int)

            y_all = np.concatenate([y_train_arr, y_test_arr])
            sem_all = np.concatenate([sem_train_arr, sem_test_arr])

            X_rows, y_rows, target_indices = build_temporal_supervised(y_all, sem_all)
            split_point = len(y_train_arr)
            train_mask = target_indices < split_point
            test_mask = target_indices >= split_point

            if train_mask.sum() < self.sequence_length + 1 or test_mask.sum() < 1:
                print(f"      ⚠️  Insufficient sequences")
                return False

            X_train_rows = X_rows[train_mask]
            X_test_rows = X_rows[test_mask]

            self.feature_scaler.fit(X_train_rows)
            X_train_scaled_rows = self.feature_scaler.transform(X_train_rows)
            X_test_scaled_rows = self.feature_scaler.transform(X_test_rows)

            y_train_log = np.log1p(np.maximum(y_rows[train_mask], 0.0))
            y_test_actual = y_rows[test_mask]

            X_train, y_train_seq, _ = self.create_sequences(X_train_scaled_rows, y_train_log)

            bridge_rows = np.vstack([X_train_scaled_rows[-(self.sequence_length - 1):], X_test_scaled_rows])
            X_test, _, _ = self.create_sequences(
                bridge_rows,
                np.zeros(len(bridge_rows), dtype=float)
            )
            X_test = X_test[:len(X_test_rows)]

            if len(X_train) < 2 or len(X_test) < 1:
                print(f"      ⚠️  Insufficient sequences")
                return False

            n_features = int(X_train.shape[2])
            
            self.model = Sequential([
                LSTM(32, activation='relu', input_shape=(self.sequence_length, n_features)),
                Dropout(0.2),
                Dense(16, activation='relu'),
                Dense(1)
            ])
            
            self.model.compile(optimizer=Adam(learning_rate=0.001), loss='mse')
            
            early_stop = EarlyStopping(monitor='val_loss', patience=5, restore_best_weights=True)
            
            history = self.model.fit(
                X_train, y_train_seq,
                epochs=30,
                batch_size=4,
                validation_split=0.2,
                callbacks=[early_stop],
                verbose=0
            )
            
            final_train_loss = float(history.history['loss'][-1])
            final_val_loss = float(history.history['val_loss'][-1])
            
            predictions_log = self.model.predict(X_test, verbose=0).flatten()
            predictions = np.expm1(predictions_log)
            
            mae = float(mean_absolute_error(y_test_actual, predictions))
            mse = float(mean_squared_error(y_test_actual, predictions))
            rmse = float(np.sqrt(mse))
            
            mask = y_test_actual != 0
            if mask.any():
                mape = float(np.mean(np.abs((y_test_actual[mask] - predictions[mask]) / y_test_actual[mask])) * 100)
            else:
                mape = 0.0

            if np.var(y_test_actual) == 0:
                r2 = np.nan
            else:
                r2 = float(r2_score(y_test_actual, predictions))
            
            self.metrics = {
                'MAE': round(mae, 2),
                'RMSE': round(rmse, 2),
                'MAPE': round(mape, 2),
                'MSE': round(mse, 2),
                'Training_Loss': round(final_train_loss, 4),
                'Validation_Loss': round(final_val_loss, 4),
                'R²': round(r2, 4) if is_valid_db_number(r2) else np.nan
            }

            self.last_semester = int(sem_all[-1])
            self.last_trend_index = int(len(y_all) - 1)
            self.semester_last_value_map = build_last_seen_semester_map(sem_all, y_all)
            
            print(f"      ✅ LSTM training successful")
            return True
            
        except Exception as e:
            print(f"      ❌ LSTM error: {str(e)}")
            return False
    
    def predict(self, y_hist, semester_hist, steps=3):
        """Generate predictions"""
        if self.model is None:
            return None
        
        try:
            history_totals = list(np.array(y_hist, dtype=float))
            history_semesters = list(np.array(semester_hist, dtype=int))
            if len(history_totals) < self.sequence_length:
                return None

            sem_last_map = dict(self.semester_last_value_map)
            current_trend = int(self.last_trend_index if self.last_trend_index is not None else len(history_totals) - 1)
            current_semester = int(self.last_semester if self.last_semester is not None else history_semesters[-1])

            latest_rows = []
            for idx in range(len(history_totals) - self.sequence_length + 1, len(history_totals)):
                sem = int(history_semesters[idx])
                lag1 = float(history_totals[idx - 1])
                same_sem = float(sem_last_map.get(sem, lag1))
                latest_rows.append(make_temporal_feature_row(lag1, same_sem, sem, idx))
                sem_last_map[sem] = float(history_totals[idx])

            scaled_seed_rows = self.feature_scaler.transform(np.array(latest_rows, dtype=float))
            current_seq = scaled_seed_rows.reshape(1, self.sequence_length - 1, -1)
            predictions = []
            
            for _ in range(steps):
                next_sem = get_next_semester(current_semester)
                next_trend = current_trend + 1
                lag1 = float(history_totals[-1])
                same_sem = float(sem_last_map.get(next_sem, lag1))

                next_row = make_temporal_feature_row(lag1, same_sem, next_sem, next_trend)
                next_row_scaled = self.feature_scaler.transform(next_row.reshape(1, -1))[0]

                full_seq = np.vstack([current_seq[0], next_row_scaled]).reshape(1, self.sequence_length, -1)
                pred_log = float(self.model.predict(full_seq, verbose=0)[0, 0])
                pred_value = max(float(np.expm1(pred_log)), 0.0)
                predictions.append(pred_value)

                history_totals.append(pred_value)
                history_semesters.append(next_sem)
                sem_last_map[next_sem] = pred_value
                current_semester = next_sem
                current_trend = next_trend

                current_seq = np.vstack([current_seq[0][1:], next_row_scaled]).reshape(1, self.sequence_length - 1, -1)
            
            return {
                'model': 'LSTM',
                'predictions': np.array(predictions, dtype=float),
                'metrics': self.metrics
            }
        except Exception as e:
            print(f"      ❌ Prediction error: {str(e)}")
            return None


def load_data():
    """Load data"""
    print("\n📂 Loading enrollment data...")
    conn = mysql.connector.connect(**DB_CONFIG)
    df = pd.read_sql("""
        SELECT program_id, academic_year, semester, male, female, 
               (male + female) as total
        FROM enrollments 
        ORDER BY program_id, academic_year, semester
    """, conn)
    conn.close()
    
    print(f"✅ Loaded {len(df)} records from {df['program_id'].nunique()} programs")
    return df


def predict_program(program_id, program_data):
    """Train models for program"""
    print(f"\n{'='*100}")
    print(f"📚 PROGRAM {program_id}: {PROGRAM_NAMES.get(program_id, 'Unknown')}")
    print(f"{'='*100}")
    
    program_data = program_data.sort_values(['academic_year', 'semester']).reset_index(drop=True)
    
    if len(program_data) < 8:
        print(f"⚠️  Insufficient data ({len(program_data)} points)")
        return None
    
    y = program_data['total'].values.astype(float)
    semesters = program_data['semester'].values.astype(int)
    
    print(f"\n📊 Data: {len(y)} points | Min: {y.min():.0f} | Max: {y.max():.0f} | Mean: {y.mean():.1f}")
    
    split_idx = int(len(y) * 0.8)
    y_train = y[:split_idx]
    y_test = y[split_idx:]
    sem_train = semesters[:split_idx]
    sem_test = semesters[split_idx:]
    
    print(f"🔀 Train: {len(y_train)} | Test: {len(y_test)}")
    
    models = {}
    
    # SARMAX
    print(f"\n{'-'*100}")
    print(f"MODEL 1: SARMAX")
    print(f"{'-'*100}")
    sarmax = SARMAXPredictor()
    if sarmax.train(y_train, y_test):
        models['SARMAX'] = sarmax
        print(f"\n   Metrics:")
        for m, v in sarmax.metrics.items():
            print(f"      {m:<20}: {v}")
        sarmax_pred = sarmax.predict(y, steps=3)
    else:
        models['SARMAX'] = None
        sarmax_pred = None
    
    # LSTM
    print(f"\n{'-'*100}")
    print(f"MODEL 2: LSTM")
    print(f"{'-'*100}")
    lstm = LSTMPredictor(sequence_length=min(4, len(y_train)//3))
    if lstm.train(y_train, y_test, sem_train, sem_test):
        models['LSTM'] = lstm
        print(f"\n   Metrics:")
        for m, v in lstm.metrics.items():
            print(f"      {m:<20}: {v}")
        lstm_pred = lstm.predict(y, semesters, steps=3)
    else:
        models['LSTM'] = None
        lstm_pred = None
    
    # Ensemble
    predictions_list = [
        sarmax_pred['predictions'] if sarmax_pred else None,
        lstm_pred['predictions'] if lstm_pred else None
    ]
    
    valid_predictions = [p for p in predictions_list if p is not None]
    
    if valid_predictions:
        ensemble_pred = np.mean(valid_predictions, axis=0)
        ensemble_confidence = get_ensemble_confidence(sarmax_pred, lstm_pred)
        print(f"\n✅ Ensemble from {len(valid_predictions)}/2 models")
        print(f"🔎 Confidence (metrics-based): {ensemble_confidence * 100:.1f}%")
    else:
        print(f"\n❌ No valid predictions")
        return None
    
    return {
        'program_id': program_id,
        'models': models,
        'predictions': ensemble_pred,
        'confidence': ensemble_confidence,
        'metrics': {
            'SARMAX': models['SARMAX'].metrics if models['SARMAX'] else {},
            'LSTM': models['LSTM'].metrics if models['LSTM'] else {}
        }
    }

def save_to_database(all_predictions):
    """Save to database"""
    print(f"\n\n{'='*100}")
    print(f"💾 SAVING TO DATABASE")
    print(f"{'='*100}")
    
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor()
    
    try:
        cursor.execute("DELETE FROM predictions WHERE academic_year >= '2026-2027'")
        cursor.execute("DELETE FROM model_metrics WHERE prediction_year >= '2026'")
        conn.commit()
        print(f"✅ Cleared existing data")
        
        pred_count = 0
        metric_count = 0
        
        for pred_result in all_predictions:
            if pred_result is None:
                continue
            
            program_id = int(pred_result['program_id'])
            
            cursor.execute(
                "SELECT SUM(male), SUM(female) FROM enrollments WHERE program_id = %s",
                (program_id,)
            )
            result = cursor.fetchone()
            if result and result[0] and result[1]:
                avg_male_ratio = float(result[0]) / float(result[0] + result[1])
            else:
                avg_male_ratio = 0.5
            
            # Save predictions - use actual column names from your table
            for sem, pred_val in enumerate(pred_result['predictions'][:3]):
                sem_num = (sem % 3) + 1
                pred_total = int(max(float(pred_val), 0))
                pred_male = int(float(pred_total * avg_male_ratio))
                pred_female = int(float(pred_total - pred_male))
                
                cursor.execute("""
                    INSERT INTO predictions 
                    (program_id, academic_year, semester, predicted_total, 
                     predicted_male, predicted_female, confidence)
                    VALUES (%s, %s, %s, %s, %s, %s, %s)
                """, (
                    int(program_id), '2026-2027', int(sem_num),
                    int(pred_total), int(pred_male), int(pred_female),
                    float(pred_result.get('confidence', 0.5))
                ))
                pred_count += 1
            
            # Save metrics
            for model_name, metrics_dict in pred_result['metrics'].items():
                for metric_name, metric_val in metrics_dict.items():
                    if not is_valid_db_number(metric_val):
                        print(f"⚠️  Skipping invalid metric {model_name}.{metric_name}: {metric_val}")
                        continue

                    cursor.execute("""
                        INSERT INTO model_metrics 
                        (program_id, model_name, metric_name, metric_value, prediction_year)
                        VALUES (%s, %s, %s, %s, %s)
                    """, (
                        int(program_id), str(model_name), str(metric_name),
                        float(metric_val), '2026-2027'
                    ))
                    metric_count += 1
        
        conn.commit()
        print(f"✅ Saved {pred_count} predictions")
        print(f"✅ Saved {metric_count} metrics")
        
    except Exception as e:
        print(f"❌ Error: {str(e)}")
        conn.rollback()
    finally:
        cursor.close()
        conn.close()


if __name__ == "__main__":
    
    df_hist = load_data()
    
    if df_hist is None:
        exit(1)
    
    print("\n" + "="*100)
    print("🚀 TRAINING MODELS")
    print("="*100)
    
    all_predictions = []
    
    for program_id in sorted(df_hist['program_id'].unique()):
        program_data = df_hist[df_hist['program_id'] == program_id].copy()
        result = predict_program(program_id, program_data)
        all_predictions.append(result)
    
    save_to_database(all_predictions)
    
    print(f"\n{'='*100}")
    print(f"✨ COMPLETE - {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print(f"{'='*100}\n")