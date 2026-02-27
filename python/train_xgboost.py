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

# Features: time trend + semester seasonality (IMPROVED - per-program time trend)
def create_features(df):
    """Create time-based features for XGBoost"""
    df = df.copy()
    df['year'] = df['academic_year'].str[:4].astype(int)
    
    # Semester seasonality (cyclical encoding)
    df['semester_sin'] = np.sin(2 * np.pi * df['semester'] / 3)
    df['semester_cos'] = np.cos(2 * np.pi * df['semester'] / 3)
    
    # Time trend PER PROGRAM (not global) - resets for each program
    df['time_trend'] = df.groupby('program_id').cumcount()
    
    # Total enrollment
    df['total'] = df['male'] + df['female']
    
    return df

conn = mysql.connector.connect(**DB_CONFIG)
df = pd.read_sql("""
    SELECT program_id, academic_year, semester, male, female
    FROM enrollments 
    WHERE academic_year NOT LIKE '%-2027'
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
    
    print(f"\n📚 Program {program_id}:")
    print(f"   Data points: {len(prog_df)}")
    
    # Feature engineering
    prog_df = create_features(prog_df)
    X = prog_df[feature_cols]
    y = prog_df['total']
    
    # Time series split (respect chronological order)
    n_splits = min(3, len(prog_df) // 2)
    if n_splits < 1:
        print(f"   ⚠️ Not enough data for time series split")
        continue
    
    tscv = TimeSeriesSplit(n_splits=n_splits)
    scores = []
    
    print(f"   🔄 Cross-validation: {n_splits} splits")
    
    for fold_idx, (train_idx, val_idx) in enumerate(tscv.split(X), 1):
        X_train, X_val = X.iloc[train_idx], X.iloc[val_idx]
        y_train, y_val = y.iloc[train_idx], y.iloc[val_idx]
        
        model = XGBRegressor(
            n_estimators=100,
            max_depth=3,
            learning_rate=0.1,
            subsample=0.8,
            colsample_bytree=0.8,
            random_state=42,
            n_jobs=-1,
            verbosity=0
        )
        
        model.fit(X_train, y_train)
        y_pred = model.predict(X_val)
        mae = mean_absolute_error(y_val, y_pred)
        r2 = r2_score(y_val, y_pred)
        scores.append(mae)
        
        print(f"      Fold {fold_idx}: MAE={mae:.1f}, R²={r2:.3f}")
    
    avg_mae = np.mean(scores)
    std_mae = np.std(scores)
    print(f"   ✅ Average MAE: {avg_mae:.1f} ± {std_mae:.1f}")
    
    # Train final model on all data
    print(f"   🏋️ Training final model on all {len(prog_df)} samples...")
    
    model = XGBRegressor(
        n_estimators=100,
        max_depth=3,
        learning_rate=0.1,
        subsample=0.8,
        colsample_bytree=0.8,
        random_state=42,
        n_jobs=-1,
        verbosity=0
    )
    model.fit(X, y)
    
    # Save model
    model_path = f'xgboost_prog_{program_id}.pkl'
    joblib.dump(model, model_path)
    print(f"   💾 Model saved: {model_path}")
    
    models[program_id] = model
    results.append({
        'program_id': program_id, 
        'mae': avg_mae, 
        'std_mae': std_mae,
        'data_points': len(prog_df),
        'time_range': f"{prog_df['academic_year'].min()} to {prog_df['academic_year'].max()}"
    })

print("\n" + "="*70)
print("🎯 MODEL PERFORMANCE SUMMARY")
print("="*70)

if results:
    results_df = pd.DataFrame(results)
    print(results_df.to_string(index=False))
    print("="*70)
    print(f"✅ Trained {len(results_df)} models successfully")
    print(f"📊 Average MAE across all programs: {results_df['mae'].mean():.1f}")
    print(f"📏 Best performer: Program {results_df.loc[results_df['mae'].idxmin(), 'program_id']:.0f}")
    print(f"📏 Worst performer: Program {results_df.loc[results_df['mae'].idxmax(), 'program_id']:.0f}")
else:
    print("❌ No models trained!")
    exit(1)

print("\n" + "="*70)
print("💾 All models saved as: xgboost_prog_*.pkl")
print("🚀 Ready for predictions! Run: python3 predict_xgboost.py")
print("="*70)