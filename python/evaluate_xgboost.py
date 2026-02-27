#!/usr/bin/env python3
import pandas as pd
import numpy as np
from xgboost import XGBRegressor
from sklearn.metrics import mean_absolute_error, mean_squared_error, r2_score
import joblib
import mysql.connector
from pathlib import Path
import warnings
warnings.filterwarnings('ignore')

print("📊 XGBoost Enrollment Prediction - ACCURACY EVALUATION")
print("="*70)

DB_CONFIG = {
    'host': 'localhost', 'user': 'root', 'password': '', 'database': 'casDB'
}

def create_features(df):
    """Create time-based features for XGBoost (per-program)"""
    df = df.copy()
    df['year'] = df['academic_year'].str[:4].astype(int)
    df['semester_sin'] = np.sin(2 * np.pi * df['semester'] / 3)
    df['semester_cos'] = np.cos(2 * np.pi * df['semester'] / 3)
    df['time_trend'] = df.groupby('program_id').cumcount()
    df['total'] = df['male'] + df['female']
    return df

def evaluate_model(model, X_test, y_test, program_id):
    """Evaluate model performance"""
    y_pred = model.predict(X_test)
    
    # Calculate metrics
    mae = mean_absolute_error(y_test, y_pred)
    rmse = np.sqrt(mean_squared_error(y_test, y_pred))
    r2 = r2_score(y_test, y_pred)
    
    # MAPE (Mean Absolute Percentage Error)
    mape = np.mean(np.abs((y_test - y_pred) / y_test)) * 100
    
    return {
        'program_id': program_id,
        'mae': mae,
        'rmse': rmse,
        'r2': r2,
        'mape': mape,
        'test_samples': len(y_test)
    }

# Load data
print("📥 Loading enrollment data...")
conn = mysql.connector.connect(**DB_CONFIG)
df = pd.read_sql("""
    SELECT program_id, academic_year, semester, male, female
    FROM enrollments 
    WHERE academic_year NOT LIKE '%-2027'
    ORDER BY program_id, academic_year, semester
""", conn)
conn.close()

print(f"Loaded {len(df)} records across {df['program_id'].nunique()} programs\n")

# Feature engineering
df = create_features(df)

feature_cols = ['time_trend', 'semester_sin', 'semester_cos', 'year']
evaluation_results = []

prog_names = {
    1: 'BA Communication', 2: 'BA English', 3: 'BA PolSci', 4: 'BLIS',
    5: 'BM Music', 6: 'BS Biology', 7: 'BSIT', 8: 'BS Social Work'
}

print("🔍 Evaluating models...\n")

for program_id in sorted(df['program_id'].unique()):
    try:
        # Load model
        model_path = f'xgboost_prog_{program_id}.pkl'
        if not Path(model_path).exists():
            print(f"⚠️  Program {program_id}: Model not found ({model_path})")
            continue
        
        model = joblib.load(model_path)
        
        # Get program data
        prog_df = df[df['program_id'] == program_id].copy().reset_index(drop=True)
        
        if len(prog_df) < 4:
            print(f"⚠️  Program {program_id}: Insufficient data ({len(prog_df)} points)")
            continue
        
        # Split: use last 2 samples for testing, rest for training context
        train_size = max(2, len(prog_df) - 2)
        X_train = prog_df[feature_cols].iloc[:train_size]
        y_train = prog_df['total'].iloc[:train_size]
        
        X_test = prog_df[feature_cols].iloc[train_size:]
        y_test = prog_df['total'].iloc[train_size:]
        
        if len(X_test) == 0:
            print(f"⚠️  Program {program_id}: Not enough data for testing")
            continue
        
        # Evaluate
        metrics = evaluate_model(model, X_test, y_test, program_id)
        evaluation_results.append(metrics)
        
        prog_name = prog_names.get(program_id, f'Program {program_id}')
        print(f"✅ {prog_name:20} | MAE: {metrics['mae']:6.1f} | RMSE: {metrics['rmse']:6.1f} | "
              f"R²: {metrics['r2']:6.3f} | MAPE: {metrics['mape']:6.1f}% | Samples: {metrics['test_samples']}")
        
    except Exception as e:
        print(f"❌ Program {program_id}: {str(e)}")

# Summary statistics
if evaluation_results:
    print("\n" + "="*70)
    print("📊 OVERALL EVALUATION SUMMARY")
    print("="*70)
    
    results_df = pd.DataFrame(evaluation_results)
    
    print(f"\n{'Metric':<20} {'Mean':<12} {'Std Dev':<12} {'Min':<12} {'Max':<12}")
    print("-"*70)
    print(f"{'MAE':<20} {results_df['mae'].mean():<12.2f} {results_df['mae'].std():<12.2f} "
          f"{results_df['mae'].min():<12.2f} {results_df['mae'].max():<12.2f}")
    print(f"{'RMSE':<20} {results_df['rmse'].mean():<12.2f} {results_df['rmse'].std():<12.2f} "
          f"{results_df['rmse'].min():<12.2f} {results_df['rmse'].max():<12.2f}")
    print(f"{'R²':<20} {results_df['r2'].mean():<12.3f} {results_df['r2'].std():<12.3f} "
          f"{results_df['r2'].min():<12.3f} {results_df['r2'].max():<12.3f}")
    print(f"{'MAPE (%)':<20} {results_df['mape'].mean():<12.2f} {results_df['mape'].std():<12.2f} "
          f"{results_df['mape'].min():<12.2f} {results_df['mape'].max():<12.2f}")
    
    print("\n" + "="*70)
    print("📈 INTERPRETATION:")
    print("="*70)
    print(f"✓ MAE: Average prediction error (~{results_df['mae'].mean():.0f} students)")
    print(f"✓ RMSE: Root mean squared error (~{results_df['rmse'].mean():.0f} students)")
    print(f"✓ R²: Proportion of variance explained ({results_df['r2'].mean():.1%})")
    print(f"✓ MAPE: Percentage error ({results_df['mape'].mean():.1f}%)")
    
    if results_df['r2'].mean() > 0.7:
        print("\n🎉 Models are performing WELL (R² > 0.7)")
    elif results_df['r2'].mean() > 0.5:
        print("\n👍 Models are performing FAIRLY (R² > 0.5)")
    else:
        print("\n⚠️  Models may need improvement (R² < 0.5)")
    
    print("\n" + "="*70)
    print("💡 RECOMMENDATIONS:")
    print("="*70)
    
    worst_program = results_df.loc[results_df['r2'].idxmin()]
    print(f"• Program {int(worst_program['program_id'])} has the worst R² ({worst_program['r2']:.3f})")
    print("  Consider: More training data, feature engineering, or hyperparameter tuning")
    
    print("\n✅ To generate 2026-2027 predictions, run:")
    print("   python3 predict_xgboost.py")
    
else:
    print("❌ No models available for evaluation!")

print("\n" + "="*70)