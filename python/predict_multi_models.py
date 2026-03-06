#!/usr/bin/env python3
"""
Multi-Model Enrollment Prediction System
Supports: SARMAX, Facebook Prophet, and LSTM
Generates predictions for multiple future years with comprehensive evaluation metrics
Saves all metrics to database for dashboard visualization
"""

import pandas as pd
import numpy as np
import joblib
import mysql.connector
from datetime import datetime
from pathlib import Path
import warnings
warnings.filterwarnings('ignore')

# ============ IMPORTS FOR MODELS ============
from statsmodels.tsa.statespace.sarimax import SARIMAX
from statsmodels.graphics.tsaplots import plot_acf, plot_pacf
from statsmodels.tsa.seasonal import seasonal_decompose
from prophet import Prophet
from tensorflow.keras.models import Sequential
from tensorflow.keras.layers import LSTM, Dense, Dropout
from tensorflow.keras.optimizers import Adam
from tensorflow.keras.callbacks import EarlyStopping
from sklearn.preprocessing import MinMaxScaler
from sklearn.metrics import (
    mean_absolute_error,
    mean_squared_error,
    mean_absolute_percentage_error,
    r2_score
)

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

# ============ EVALUATION METRICS CLASS ============
class ModelEvaluator:
    """Calculate and store model evaluation metrics"""
    
    @staticmethod
    def calculate_metrics(y_true, y_pred, model_name=""):
        """
        Calculate comprehensive evaluation metrics
        
        Metrics included:
        - MAE (Mean Absolute Error): Average absolute difference
        - RMSE (Root Mean Squared Error): Square root of average squared errors
        - MAPE (Mean Absolute Percentage Error): Average percentage error
        - R² (Coefficient of Determination): Proportion of variance explained (0-1)
        - RMSLE (Root Mean Squared Log Error): For non-negative data
        - Theil-U (Theil Inequality Coefficient): Relative to naive forecast
        """
        
        # Ensure numpy arrays
        y_true = np.array(y_true, dtype=float)
        y_pred = np.array(y_pred, dtype=float)
        
        # Ensure non-negative predictions (enrollment can't be negative)
        y_pred = np.maximum(y_pred, 0)
        
        mae = mean_absolute_error(y_true, y_pred)
        mse = mean_squared_error(y_true, y_pred)
        rmse = np.sqrt(mse)
        
        # MAPE: avoid division by zero
        mask = y_true != 0
        if mask.any():
            mape = np.mean(np.abs((y_true[mask] - y_pred[mask]) / y_true[mask])) * 100
        else:
            mape = np.inf
        
        # R²
        r2 = r2_score(y_true, y_pred)
        
        # RMSLE: Root Mean Squared Log Error
        log_true = np.log1p(y_true)
        log_pred = np.log1p(y_pred)
        rmsle = np.sqrt(mean_squared_error(log_true, log_pred))
        
        # Theil U: relative to naive forecast (persistence)
        naive_pred = y_true[:-1]
        theil_u = rmse / np.sqrt(mean_squared_error(y_true[1:], naive_pred))
        
        metrics = {
            'MAE': round(mae, 2),
            'RMSE': round(rmse, 2),
            'MAPE': round(mape, 2),
            'R²': round(r2, 4),
            'RMSLE': round(rmsle, 4),
            'Theil_U': round(theil_u, 4)
        }
        
        return metrics
    
    @staticmethod
    def print_metrics(metrics, model_name="Model"):
        """Pretty print metrics"""
        print(f"\n   📊 {model_name} Evaluation Metrics:")
        print(f"      MAE:       {metrics['MAE']:<8}   (Mean Absolute Error)")
        print(f"      RMSE:      {metrics['RMSE']:<8}   (Root Mean Squared Error)")
        print(f"      MAPE:      {metrics['MAPE']:<8}%  (Mean Absolute Percentage Error)")
        print(f"      R²:        {metrics['R²']:<8}   (Coefficient of Determination)")
        print(f"      RMSLE:     {metrics['RMSLE']:<8}   (Root Mean Squared Log Error)")
        print(f"      Theil-U:   {metrics['Theil_U']:<8}   (Theil Inequality Coefficient)")


# ============ SARMAX MODEL ============
class SARMAXPredictor:
    """SARMAX (Seasonal ARIMA) for time series forecasting"""
    
    def __init__(self, order=(1,1,1), seasonal_order=(1,1,1,3)):
        """
        SARMAX Parameters:
        - order: (p,d,q) - AR, differencing, MA
        - seasonal_order: (P,D,Q,s) - Seasonal AR, D, MA, season length
        s=3 for three semesters per academic year
        """
        self.order = order
        self.seasonal_order = seasonal_order
        self.model = None
        self.fitted_model = None
        self.scaler = MinMaxScaler()
        self.metrics = {}
    
    def train(self, y_train, y_test):
        """Train SARMAX model"""
        print(f"      🔧 Training SARMAX model...")
        
        try:
            # Fit SARMAX model
            self.model = SARIMAX(
                y_train,
                order=self.order,
                seasonal_order=self.seasonal_order,
                enforce_stationarity=False,
                enforce_invertibility=False
            )
            self.fitted_model = self.model.fit(disp=False, maxiter=500)
            
            # Evaluate on test set
            predictions = self.fitted_model.fittedvalues[-len(y_test):]
            
            # If predictions not enough, make forecast
            if len(predictions) < len(y_test):
                forecast = self.fitted_model.get_forecast(steps=len(y_test))
                predictions = forecast.predicted_mean.values
            
            self.metrics = ModelEvaluator.calculate_metrics(y_test, predictions, "SARMAX")
            return True
            
        except Exception as e:
            print(f"      ❌ SARMAX training error: {str(e)}")
            return False
    
    def predict(self, steps=1):
        """Generate future predictions"""
        if self.fitted_model is None:
            return None
        
        try:
            forecast = self.fitted_model.get_forecast(steps=steps)
            predictions = forecast.predicted_mean.values
            conf_int = forecast.conf_int()
            
            return {
                'predictions': np.maximum(predictions, 0),  # Ensure non-negative
                'lower_ci': np.maximum(conf_int.iloc[:, 0].values, 0),
                'upper_ci': np.maximum(conf_int.iloc[:, 1].values, 0),
                'metrics': self.metrics
            }
        except Exception as e:
            print(f"      ❌ SARMAX prediction error: {str(e)}")
            return None


# ============ FACEBOOK PROPHET MODEL ============
class ProphetPredictor:
    """Facebook Prophet for time series forecasting with seasonality"""
    
    def __init__(self, yearly_seasonality=False, weekly_seasonality=False):
        """
        Prophet Parameters:
        - yearly_seasonality: Annual patterns
        - weekly_seasonality: Weekly patterns (typically False for academic data)
        """
        self.yearly_seasonality = yearly_seasonality
        self.weekly_seasonality = weekly_seasonality
        self.model = None
        self.metrics = {}
        self.scaler = MinMaxScaler()
    
    def prepare_data(self, df, value_col):
        """Prepare data for Prophet (requires 'ds' and 'y' columns)"""
        prophet_df = pd.DataFrame({
            'ds': pd.date_range(start='2015-01-01', periods=len(df), freq='3MS'),
            'y': df[value_col].values
        })
        return prophet_df
    
    def train(self, y_train, y_test):
        """Train Prophet model"""
        print(f"      🔧 Training Prophet model...")
        
        try:
            # Create training dataframe
            train_df = self.prepare_data(
                pd.DataFrame({'value': y_train}),
                'value'
            )
            
            # Initialize and fit Prophet
            self.model = Prophet(
                yearly_seasonality=self.yearly_seasonality,
                weekly_seasonality=self.weekly_seasonality,
                interval_width=0.95,
                stan_backend='cmdstanpy'
            )
            self.model.fit(train_df)
            
            # Forecast on test period
            future = self.model.make_future_dataframe(periods=len(y_test), freq='3MS')
            forecast = self.model.predict(future)
            
            # Get predictions for test period
            predictions = forecast['yhat'].tail(len(y_test)).values
            
            self.metrics = ModelEvaluator.calculate_metrics(y_test, predictions, "Prophet")
            return True
            
        except Exception as e:
            print(f"      ❌ Prophet training error: {str(e)}")
            return False
    
    def predict(self, steps=1):
        """Generate future predictions"""
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


# ============ LSTM MODEL ============
class LSTMPredictor:
    """LSTM (Long Short-Term Memory) Neural Network for time series"""
    
    def __init__(self, sequence_length=4, lstm_units=32, epochs=100, batch_size=8):
        """
        LSTM Parameters:
        - sequence_length: Number of past time steps to use
        - lstm_units: Number of LSTM cells
        - epochs: Number of training epochs
        - batch_size: Batch size for training
        """
        self.sequence_length = sequence_length
        self.lstm_units = lstm_units
        self.epochs = epochs
        self.batch_size = batch_size
        self.model = None
        self.scaler = MinMaxScaler(feature_range=(0, 1))
        self.metrics = {}
    
    def create_sequences(self, data):
        """Create sequences for LSTM input"""
        X, y = [], []
        for i in range(len(data) - self.sequence_length):
            X.append(data[i:i + self.sequence_length])
            y.append(data[i + self.sequence_length])
        return np.array(X), np.array(y)
    
    def train(self, y_train, y_test):
        """Train LSTM model"""
        print(f"      🔧 Training LSTM model...")
        
        try:
            # Normalize data
            y_all = np.concatenate([y_train, y_test]).reshape(-1, 1)
            scaled_data = self.scaler.fit_transform(y_all)
            
            train_scaled = scaled_data[:len(y_train)]
            test_scaled = scaled_data[len(y_train):]
            
            # Create sequences
            X_train, y_train_seq = self.create_sequences(train_scaled)
            X_test, y_test_seq = self.create_sequences(test_scaled)
            
            if len(X_train) < 2 or len(X_test) < 2:
                print(f"      ⚠️  Insufficient data for LSTM")
                return False
            
            # Build LSTM model
            self.model = Sequential([
                LSTM(self.lstm_units, activation='relu', 
                     input_shape=(self.sequence_length, 1)),
                Dropout(0.2),
                Dense(16, activation='relu'),
                Dense(1)
            ])
            
            self.model.compile(optimizer=Adam(learning_rate=0.001), loss='mse')
            
            # Train with early stopping
            early_stop = EarlyStopping(monitor='val_loss', patience=10, restore_best_weights=True)
            
            self.model.fit(
                X_train, y_train_seq,
                epochs=self.epochs,
                batch_size=self.batch_size,
                validation_split=0.2,
                callbacks=[early_stop],
                verbose=0
            )
            
            # Evaluate on test set
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
        """Generate future predictions"""
        if self.model is None:
            return None
        
        try:
            # Prepare initial sequence
            y_hist_all = np.concatenate([y_hist]).reshape(-1, 1)
            scaled_hist = self.scaler.transform(y_hist_all)
            
            current_seq = scaled_hist[-self.sequence_length:].reshape(1, self.sequence_length, 1)
            predictions = []
            
            # Generate predictions iteratively
            for _ in range(steps):
                pred_scaled = self.model.predict(current_seq, verbose=0)[0, 0]
                predictions.append(pred_scaled)
                
                # Update sequence for next prediction
                new_seq = np.append(current_seq[0, 1:, 0], pred_scaled)
                current_seq = new_seq.reshape(1, self.sequence_length, 1)
            
            # Inverse transform
            predictions_array = np.array(predictions).reshape(-1, 1)
            predictions = self.scaler.inverse_transform(predictions_array)
            
            return {
                'predictions': np.maximum(predictions.flatten(), 0),
                'lower_ci': None,  # LSTM doesn't provide CI directly
                'upper_ci': None,
                'metrics': self.metrics
            }
        except Exception as e:
            print(f"      ❌ LSTM prediction error: {str(e)}")
            return None


# ============ MAIN PREDICTION ENGINE ============
def load_enrollment_data():
    """Load historical enrollment data from database"""
    print("\n📂 Loading historical data...")
    
    conn = mysql.connector.connect(**DB_CONFIG)
    df = pd.read_sql("""
        SELECT program_id, academic_year, semester, male, female, 
               (male + female) as total
        FROM enrollments 
        WHERE academic_year NOT LIKE '%-2027'
        ORDER BY program_id, academic_year, semester
    """, conn)
    conn.close()
    
    if df.empty:
        print("❌ No historical enrollment data found!")
        return None
    
    print(f"✅ Loaded {len(df)} records from {df['program_id'].nunique()} programs")
    return df


def predict_for_program(program_id, program_data, future_years=1):
    """
    Generate predictions for a single program using all three models
    
    Args:
        program_id: Program identifier
        program_data: Historical data for program
        future_years: Number of years to predict into future
    
    Returns:
        Dictionary with predictions from all models
    """
    print(f"\n{'='*60}")
    print(f"📚 Program {program_id}: {PROGRAM_NAMES.get(program_id, 'Unknown')}")
    print(f"{'='*60}")
    
    # Sort by academic year and semester
    program_data = program_data.sort_values(['academic_year', 'semester']).reset_index(drop=True)
    
    if len(program_data) < 5:
        print(f"⚠️  Insufficient historical data ({len(program_data)} points)")
        return None
    
    # Extract total enrollment time series
    y = program_data['total'].values.astype(float)
    
    # Split: 80% train, 20% test
    split_idx = int(len(y) * 0.8)
    y_train = y[:split_idx]
    y_test = y[split_idx:]
    
    print(f"\n   📊 Data Summary:")
    print(f"      Total points: {len(y)} | Train: {len(y_train)} | Test: {len(y_test)}")
    print(f"      Min: {y.min():.0f} | Max: {y.max():.0f} | Mean: {y.mean():.0f}")
    
    # ===== SARMAX =====
    print(f"\n   🔮 MODEL 1: SARMAX (Seasonal ARIMA)")
    sarmax_predictor = SARMAXPredictor()
    sarmax_success = sarmax_predictor.train(y_train, y_test)
    
    if sarmax_success:
        sarmax_pred = sarmax_predictor.predict(steps=future_years*3)  # 3 semesters per year
        ModelEvaluator.print_metrics(sarmax_pred['metrics'], "SARMAX")
    else:
        sarmax_pred = None
        print(f"      ⚠️  SARMAX model could not be trained")
    
    # ===== PROPHET =====
    print(f"\n   🔮 MODEL 2: Facebook Prophet")
    prophet_predictor = ProphetPredictor()
    prophet_success = prophet_predictor.train(y_train, y_test)
    
    if prophet_success:
        prophet_pred = prophet_predictor.predict(steps=future_years*3)
        ModelEvaluator.print_metrics(prophet_pred['metrics'], "Prophet")
    else:
        prophet_pred = None
        print(f"      ⚠️  Prophet model could not be trained")
    
    # ===== LSTM =====
    print(f"\n   🔮 MODEL 3: LSTM (Neural Network)")
    lstm_predictor = LSTMPredictor(sequence_length=min(4, len(y_train)//2))
    lstm_success = lstm_predictor.train(y_train, y_test)
    
    if lstm_success:
        lstm_pred = lstm_predictor.predict(y, steps=future_years*3)
        ModelEvaluator.print_metrics(lstm_pred['metrics'], "LSTM")
    else:
        lstm_pred = None
        print(f"      ⚠️  LSTM model could not be trained")
    
    # ===== ENSEMBLE PREDICTION =====
    print(f"\n   📈 ENSEMBLE FORECAST (Average of successful models):")
    
    predictions_list = [
        sarmax_pred['predictions'] if sarmax_pred else None,
        prophet_pred['predictions'] if prophet_pred else None,
        lstm_pred['predictions'] if lstm_pred else None
    ]
    
    valid_predictions = [p for p in predictions_list if p is not None]
    
    if valid_predictions:
        ensemble_pred = np.mean(valid_predictions, axis=0)
        print(f"      ✅ Ensemble predictions: {len(valid_predictions)} models combined")
    else:
        print(f"      ❌ No valid predictions from any model")
        return None
    
    return {
        'program_id': program_id,
        'program_name': PROGRAM_NAMES.get(program_id, f'Program {program_id}'),
        'sarmax': sarmax_pred,
        'prophet': prophet_pred,
        'lstm': lstm_pred,
        'ensemble': ensemble_pred,
        'future_years': future_years,
        'historical_data': y
    }


def save_predictions_to_db(all_predictions, future_years=1):
    """Save predictions to database"""
    print(f"\n\n{'='*80}")
    print(f"💾 SAVING PREDICTIONS TO DATABASE")
    print(f"{'='*80}")
    
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor()
    
    try:
        # Clear existing predictions for target years
        for year_offset in range(future_years):
            target_year = 2026 + year_offset
            cursor.execute(
                f"DELETE FROM predictions WHERE academic_year LIKE '{target_year}-%'"
            )
        conn.commit()
        print(f"✅ Cleared existing predictions")
        
        # Insert new predictions
        inserted_count = 0
        
        for pred_result in all_predictions:
            if pred_result is None:
                continue
            
            program_id = pred_result['program_id']
            ensemble_preds = pred_result['ensemble']
            
            # Create one prediction per future semester
            base_year = 2026
            for sem_offset, pred_value in enumerate(ensemble_preds[:future_years*3]):
                sem = (sem_offset % 3) + 1
                year_offset = sem_offset // 3
                academic_year = f"{base_year + year_offset}-{base_year + year_offset + 1}"
                
                # Calculate male/female split from historical average
                # (This should use the full enrollment dataset, not just predictions)
                # For now, we'll fetch from database
                conn_temp = mysql.connector.connect(**DB_CONFIG)
                cursor_temp = conn_temp.cursor()
                cursor_temp.execute(
                    "SELECT male, female FROM enrollments WHERE program_id = %s",
                    (program_id,)
                )
                enroll_data = cursor_temp.fetchall()
                cursor_temp.close()
                conn_temp.close()
                
                if enroll_data:
                    total_male = sum(row[0] for row in enroll_data)
                    total_female = sum(row[1] for row in enroll_data)
                    total_all = total_male + total_female
                    avg_male_ratio = total_male / total_all if total_all > 0 else 0.5
                else:
                    avg_male_ratio = 0.5
                
                pred_total = int(max(pred_value, 0))
                pred_male = int(pred_total * avg_male_ratio)
                pred_female = pred_total - pred_male
                
                # Determine model confidence (based on R² average)
                models_r2 = []
                if pred_result['sarmax'] and pred_result['sarmax'].get('metrics'):
                    models_r2.append(max(pred_result['sarmax']['metrics'].get('R²', 0), 0))
                if pred_result['prophet'] and pred_result['prophet'].get('metrics'):
                    models_r2.append(max(pred_result['prophet']['metrics'].get('R²', 0), 0))
                if pred_result['lstm'] and pred_result['lstm'].get('metrics'):
                    models_r2.append(max(pred_result['lstm']['metrics'].get('R²', 0), 0))
                
                confidence = np.mean(models_r2) if models_r2 else 0.5
                
                cursor.execute("""
                    INSERT INTO predictions 
                    (program_id, academic_year, semester, predicted_total, 
                     predicted_male, predicted_female, confidence, model_ensemble)
                    VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
                """, (
                    int(program_id),
                    academic_year,
                    int(sem),
                    pred_total,
                    pred_male,
                    pred_female,
                    float(confidence),
                    'SARMAX+Prophet+LSTM'
                ))
                
                inserted_count += 1
        
        conn.commit()
        print(f"✅ Saved {inserted_count} predictions to database")
        
    except Exception as e:
        print(f"❌ Database error: {str(e)}")
        conn.rollback()
    
    finally:
        cursor.close()
        conn.close()


def save_model_metrics_to_db(all_predictions):
    """Save individual model metrics to database for dashboard visualization"""
    print(f"\n💾 SAVING MODEL METRICS TO DATABASE")
    
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor()
    
    try:
        # Clear existing metrics for 2026+
        cursor.execute("DELETE FROM model_metrics WHERE prediction_year >= '2026'")
        conn.commit()
        print(f"✅ Cleared existing metrics")
        
        inserted_count = 0
        
        for pred_result in all_predictions:
            if pred_result is None:
                continue
            
            program_id = pred_result['program_id']
            prediction_year = f"{2026}-{2026+pred_result['future_years']-1}"
            
            # Save SARMAX metrics
            if pred_result['sarmax'] and pred_result['sarmax'].get('metrics'):
                metrics = pred_result['sarmax']['metrics']
                for metric_name, metric_value in metrics.items():
                    cursor.execute("""
                        INSERT INTO model_metrics 
                        (program_id, model_name, metric_name, metric_value, prediction_year)
                        VALUES (%s, %s, %s, %s, %s)
                    """, (
                        int(program_id),
                        'SARMAX',
                        str(metric_name),
                        float(metric_value),
                        prediction_year
                    ))
                    inserted_count += 1
            
            # Save Prophet metrics
            if pred_result['prophet'] and pred_result['prophet'].get('metrics'):
                metrics = pred_result['prophet']['metrics']
                for metric_name, metric_value in metrics.items():
                    cursor.execute("""
                        INSERT INTO model_metrics 
                        (program_id, model_name, metric_name, metric_value, prediction_year)
                        VALUES (%s, %s, %s, %s, %s)
                    """, (
                        int(program_id),
                        'Prophet',
                        str(metric_name),
                        float(metric_value),
                        prediction_year
                    ))
                    inserted_count += 1
            
            # Save LSTM metrics
            if pred_result['lstm'] and pred_result['lstm'].get('metrics'):
                metrics = pred_result['lstm']['metrics']
                for metric_name, metric_value in metrics.items():
                    cursor.execute("""
                        INSERT INTO model_metrics 
                        (program_id, model_name, metric_name, metric_value, prediction_year)
                        VALUES (%s, %s, %s, %s, %s)
                    """, (
                        int(program_id),
                        'LSTM',
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
    """Print detailed summary of all models' performance"""
    print(f"\n\n{'='*80}")
    print(f"📊 MODEL PERFORMANCE SUMMARY")
    print(f"{'='*80}")
    
    print(f"\n✅ Processed {len(all_predictions)} programs")
    print(f"✅ Generated predictions with 3-model ensemble approach")
    
    print(f"\n{'='*80}")
    print(f"📈 MODEL COMPARISON")
    print(f"{'='*80}")
    
    print(f"\n1️⃣  SARMAX (Seasonal ARIMA)")
    print(f"   ├─ Type: Classical time series")
    print(f"   ├─ Order: (p=1, d=1, q=1)")
    print(f"   ├─ Seasonality: (P=1, D=1, Q=1, s=3)")
    print(f"   ├─ Pros: ✓ Fast, ✓ Interpretable, ✓ Confidence intervals")
    print(f"   └─ Cons: ✗ Assumes stationarity, ✗ Limited to linear patterns")
    
    print(f"\n2️⃣  Facebook Prophet")
    print(f"   ├─ Type: Trend + Seasonality decomposition")
    print(f"   ├─ Yearly Seasonality: Disabled")
    print(f"   ├─ Weekly Seasonality: Disabled")
    print(f"   ├─ Pros: ✓ Robust, ✓ Auto changepoint detection, ✓ Handles missing data")
    print(f"   └─ Cons: ✗ Slower training (Stan), ✗ May underestimate CI")
    
    print(f"\n3️⃣  LSTM (Deep Learning)")
    print(f"   ├─ Type: Recurrent Neural Network")
    print(f"   ├─ Architecture: 32 LSTM cells → 16 Dense → 1 Output")
    print(f"   ├─ Sequence Length: 4 timesteps")
    print(f"   ├─ Pros: ✓ Learns complex patterns, ✓ No stationarity assumption, ✓ Flexible")
    print(f"   └─ Cons: ✗ 'Black box', ✗ Needs more data (20+), ✗ Slower (10-30s/program)")
    
    print(f"\n{'='*80}")
    print(f"📊 EVALUATION METRICS EXPLAINED")
    print(f"{'='*80}")
    
    print(f"\n📌 MAE (Mean Absolute Error)")
    print(f"   Formula: mean(|actual - predicted|)")
    print(f"   Units: Same as data (students)")
    print(f"   Interpretation: Average error magnitude")
    print(f"   Better if: Lower value")
    
    print(f"\n📌 RMSE (Root Mean Squared Error)")
    print(f"   Formula: sqrt(mean((actual - predicted)²))")
    print(f"   Units: Same as data (students)")
    print(f"   Interpretation: Error penalizing outliers more")
    print(f"   Better if: Lower value")
    
    print(f"\n📌 MAPE (Mean Absolute Percentage Error)")
    print(f"   Formula: mean(|actual - predicted| / |actual|) × 100%")
    print(f"   Units: Percentage (%)")
    print(f"   Interpretation: Relative error (easier to compare across programs)")
    print(f"   Better if: Lower value (3-8% = excellent, 10-15% = good)")
    
    print(f"\n📌 R² (Coefficient of Determination)")
    print(f"   Formula: 1 - (SS_res / SS_tot)")
    print(f"   Range: -∞ to 1.0")
    print(f"   Interpretation: Proportion of variance explained")
    print(f"   Better if: Higher value (0.85+ = excellent, 0.7-0.85 = good)")
    
    print(f"\n📌 RMSLE (Root Mean Squared Log Error)")
    print(f"   Formula: sqrt(mean((log(1+actual) - log(1+predicted))²))")
    print(f"   Units: Log scale")
    print(f"   Interpretation: Penalizes underestimation more")
    print(f"   Better if: Lower value")
    
    print(f"\n📌 Theil-U (Theil Inequality Coefficient)")
    print(f"   Formula: RMSE / RMSE_naive")
    print(f"   Range: 0 to ∞")
    print(f"   Interpretation: Model vs. naive forecast (persistence)")
    print(f"   Better if: < 1.0 (0.5 = 50% better than naive)")
    
    print(f"\n{'='*80}")
    print(f"✨ ENSEMBLE APPROACH")
    print(f"{'='*80}")
    print(f"\nFormula: Ensemble = (SARMAX + Prophet + LSTM) / 3")
    print(f"\nBenefits:")
    print(f"  ✓ Reduces variance from individual model errors")
    print(f"  ✓ More robust to model-specific failures")
    print(f"  ✓ Captures strengths of all approaches")
    print(f"  ✓ Better generalization than any single model")
    print(f"\nConfidence Scoring:")
    print(f"  • Average R² from successful models")
    print(f"  • Indicates reliability of ensemble prediction")
    print(f"  • Higher = more confident forecast")


# ============ MAIN EXECUTION ============
if __name__ == "__main__":
    
    # Load data
    df_hist = load_enrollment_data()
    
    if df_hist is None:
        exit(1)
    
    # Get number of years to predict
    print("\n" + "="*80)
    print("🔮 MULTI-YEAR PREDICTION CONFIGURATION")
    print("="*80)
    
    future_years = 1  # Can be changed to 2, 3, etc. for multi-year predictions
    print(f"\n📅 Predicting for {future_years} future year(s)")
    print(f"   Models will generate {future_years * 3} semester-level predictions")
    
    # Generate predictions for each program
    all_predictions = []
    
    for program_id in sorted(df_hist['program_id'].unique()):
        program_data = df_hist[df_hist['program_id'] == program_id].copy()
        
        result = predict_for_program(program_id, program_data, future_years=future_years)
        all_predictions.append(result)
    
    # Save to database 
    save_predictions_to_db(all_predictions, future_years=future_years)
    
    # Save model metrics
    save_model_metrics_to_db(all_predictions)
    
    # Print comprehensive summary
    print_model_performance_summary(all_predictions)
    
    # ===== FINAL SUMMARY =====
    print(f"\n\n{'='*80}")
    print(f"✨ PREDICTION COMPLETE - {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print(f"{'='*80}")
    
    print(f"\n📊 Results Summary:")
    successful = sum(1 for p in all_predictions if p is not None)
    print(f"   ✅ {successful}/{len(all_predictions)} programs processed successfully")
    print(f"   📈 Total predictions saved: {successful * future_years * 3}")
    print(f"   🤖 Model metrics saved: {successful * 3 * 6} (3 models × 6 metrics each)")
    
    print(f"\n📱 Dashboard Access:")
    print(f"   🌐 http://localhost/enrollment-tracker/dashboard.php?login=1")
    print(f"   📌 Username: admin")
    print(f"   🔐 Password: admin123")
    
    print(f"\n📋 View in Dashboard:")
    print(f"   1. Overview Tab → All Programs charts")
    print(f"   2. Single-Year Predictions → Model comparison & metrics")
    print(f"   3. Multi-Year Predictions → Forecast 1-5 years ahead")
    print(f"   4. Model Metrics → Detailed architecture info")
    
    print(f"\n💾 Database Tables:")
    print(f"   • predictions: {successful * future_years * 3} records")
    print(f"   • model_metrics: {successful * 3 * 6} records")
    
    print(f"\n{'='*80}\n")
