#!/usr/bin/env python3
import pandas as pd
import numpy as np
from xgboost import XGBRegressor
from sklearn.model_selection import TimeSeriesSplit
from sklearn.metrics import mean_absolute_error, r2_score
import joblib
import mysql.connector
from pathlib import Path
import warnings
warnings.filterwarnings('ignore')

print("🎓 XGBoost Enrollment Prediction - TRAINING MODELS")
print("🤖 Building per-program models (BSIT, PolSci, Biology, etc.)")

DB_CONFIG = {
    'host': 'localhost', 'user': 'root', 'password': '', 'database': 'casDB'
}

# Features: time trend + semester seasonality
def create_features(df):
    """Create time-based features for XGBoost"""
    df = df.copy()
    df['year'] = df['academic_year'].str[:4].astype(int)
    df['semester_sin'] = np.sin(2 * np.pi * df['semester'] / 3)
    df['semester_cos'] = np.cos(2 * np.pi * df['semester'] / 3)
    df['time_trend'] = df.groupby('program_id').cumcount()  # Sequential semesters
    df['total'] = df['male'] + df['female']
    return df

conn = mysql.connector.connect(**DB_CONFIG)
df = pd.read_sql("""
    SELECT program_id, academic_year, semester, male, female
    FROM enrollments 
    ORDER BY program_id, academic_year, semester
""", conn)

conn.close()

if df.empty:
    print("❌ No enrollment data found! Run import_excel.py first.")
    exit(1)

print(f"📊 Found {len(df)} records across {df['program_id'].nunique()} programs")

# Train separate model per program
models = {}
feature_cols = ['time_trend', 'semester_sin', 'semester_cos', 'year']
results = []

for program_id in sorted(df['program_id'].unique()):
    prog_df = df[df['program_id'] == program_id].copy()
    
    if len(prog_df) < 4:  # Need minimum data
        print(f"⚠️ Program {program_id}: insufficient data ({len(prog_df)} points)")
        continue
    
    # Feature engineering
    prog_df = create_features(prog_df)
    X = prog_df[feature_cols]
    y = prog_df['total']
    
    # Time series split (respect chronological order)
    tscv = TimeSeriesSplit(n_splits=min(3, len(prog_df)//2))
    scores = []
    
    for train_idx, val_idx in tscv.split(X):
        X_train, X_val = X.iloc[train_idx], X.iloc[val_idx]
        y_train, y_val = y.iloc[train_idx], y.iloc[val_idx]
        
        model = XGBRegressor(
            n_estimators=100,
            max_depth=3,
            learning_rate=0.1,
            subsample=0.8,
            colsample_bytree=0.8,
            random_state=42,
            n_jobs=-1
        )
        
        model.fit(X_train, y_train)
        y_pred = model.predict(X_val)
        mae = mean_absolute_error(y_val, y_pred)
        scores.append(mae)
    
    avg_mae = np.mean(scores)
    print(f"✅ Program {program_id}: MAE={avg_mae:.0f} (CV)")
    
    # Train final model
    model = XGBRegressor(
        n_estimators=100,
        max_depth=3,
        learning_rate=0.1,
        subsample=0.8,
        colsample_bytree=0.8,
        random_state=42,
        n_jobs=-1
    )
    model.fit(X, y)
    
    models[program_id] = model
    joblib.dump(model, f'xgboost_prog_{program_id}.pkl')
    results.append({'program_id': program_id, 'mae': avg_mae, 'data_points': len(prog_df)})

print("\n🎯 MODEL PERFORMANCE:")
results_df = pd.DataFrame(results)
print(results_df)

print("\n💾 Models saved: xgboost_prog_*.pkl")
print("🚀 Ready for predictions!")
