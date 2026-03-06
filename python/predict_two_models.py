#!/usr/bin/env python3
"""
SARMAX + LSTM Enrollment Prediction System
Skipping Prophet due to installation issues
"""

import pandas as pd
import numpy as np
import mysql.connector
from datetime import datetime
import warnings
warnings.filterwarnings('ignore')

from sklearn.metrics import (
    mean_absolute_error,
    mean_squared_error,
    mean_absolute_percentage_error,
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
    from sklearn.preprocessing import MinMaxScaler
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
        self.scaler = MinMaxScaler(feature_range=(0, 1))
        self.metrics = {}
    
    def create_sequences(self, data):
        """Create sequences"""
        X, y = [], []
        for i in range(len(data) - self.sequence_length):
            X.append(data[i:i + self.sequence_length])
            y.append(data[i + self.sequence_length])
        return np.array(X), np.array(y)
    
    def train(self, y_train, y_test):
        """Train LSTM"""
        print(f"\n   🔧 Training LSTM...")
        
        if not LSTM_AVAILABLE:
            print(f"      ⚠️  LSTM not available")
            return False
        
        try:
            y_all = np.concatenate([y_train, y_test]).reshape(-1, 1).astype(float)
            scaled_data = self.scaler.fit_transform(y_all)
            
            train_scaled = scaled_data[:len(y_train)]
            test_scaled = scaled_data[len(y_train):]
            
            X_train, y_train_seq = self.create_sequences(train_scaled)
            X_test, y_test_seq = self.create_sequences(test_scaled)
            
            if len(X_train) < 2 or len(X_test) < 1:
                print(f"      ⚠️  Insufficient sequences")
                return False
            
            self.model = Sequential([
                LSTM(32, activation='relu', input_shape=(self.sequence_length, 1)),
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
            
            predictions_scaled = self.model.predict(X_test, verbose=0)
            predictions = self.scaler.inverse_transform(predictions_scaled)
            y_test_actual = y_test[self.sequence_length:]
            
            mae = float(mean_absolute_error(y_test_actual, predictions.flatten()))
            mse = float(mean_squared_error(y_test_actual, predictions.flatten()))
            rmse = float(np.sqrt(mse))
            
            mask = y_test_actual != 0
            if mask.any():
                mape = float(np.mean(np.abs((y_test_actual[mask] - predictions.flatten()[mask]) / y_test_actual[mask])) * 100)
            else:
                mape = 0.0
            
            r2 = float(r2_score(y_test_actual, predictions.flatten()))
            
            self.metrics = {
                'MAE': round(mae, 2),
                'RMSE': round(rmse, 2),
                'MAPE': round(mape, 2),
                'MSE': round(mse, 2),
                'Training_Loss': round(final_train_loss, 4),
                'Validation_Loss': round(final_val_loss, 4),
                'R²': round(r2, 4)
            }
            
            print(f"      ✅ LSTM training successful")
            return True
            
        except Exception as e:
            print(f"      ❌ LSTM error: {str(e)}")
            return False
    
    def predict(self, y_hist, steps=3):
        """Generate predictions"""
        if self.model is None:
            return None
        
        try:
            y_hist_all = np.array(y_hist).reshape(-1, 1).astype(float)
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
                'model': 'LSTM',
                'predictions': np.maximum(predictions.flatten().astype(float), 0),
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
    
    print(f"\n📊 Data: {len(y)} points | Min: {y.min():.0f} | Max: {y.max():.0f} | Mean: {y.mean():.1f}")
    
    split_idx = int(len(y) * 0.8)
    y_train = y[:split_idx]
    y_test = y[split_idx:]
    
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
    if lstm.train(y_train, y_test):
        models['LSTM'] = lstm
        print(f"\n   Metrics:")
        for m, v in lstm.metrics.items():
            print(f"      {m:<20}: {v}")
        lstm_pred = lstm.predict(y, steps=3)
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
        print(f"\n✅ Ensemble from {len(valid_predictions)}/2 models")
    else:
        print(f"\n❌ No valid predictions")
        return None
    
    return {
        'program_id': program_id,
        'models': models,
        'predictions': ensemble_pred,
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
                    float(0.85)
                ))
                pred_count += 1
            
            # Save metrics
            for model_name, metrics_dict in pred_result['metrics'].items():
                for metric_name, metric_val in metrics_dict.items():
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