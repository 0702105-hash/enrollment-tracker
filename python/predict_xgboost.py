#!/usr/bin/env python3
import pandas as pd
import numpy as np
import joblib
import mysql.connector
from datetime import datetime
from pathlib import Path
import warnings
warnings.filterwarnings('ignore')

print("🔮 XGBoost Enrollment PREDICTIONS - 2026-2027 Academic Year")
print("=" * 60)

DB_CONFIG = {
    'host': 'localhost', 'user': 'root', 'password': '', 'database': 'casDB'
}

def create_features_for_prediction(prog_df, target_year=2026, target_sem=1):
    """Create features for prediction based on program's historical data"""
    prog_df = prog_df.copy().sort_values(['academic_year', 'semester'])
    
    # Extract year components
    prog_df['year'] = prog_df['academic_year'].str[:4].astype(int)
    
    # Time trend: count of observations within THIS program
    # This resets per program, so each program has its own timeline
    prog_df['time_trend'] = range(len(prog_df))
    
    # Semester seasonality (cyclical encoding)
    prog_df['semester_sin'] = np.sin(2 * np.pi * prog_df['semester'] / 3)
    prog_df['semester_cos'] = np.cos(2 * np.pi * prog_df['semester'] / 3)
    
    # For prediction: next time_trend value
    next_time_trend = len(prog_df)  # After all existing observations
    next_year_sin = np.sin(2 * np.pi * target_sem / 3)
    next_year_cos = np.cos(2 * np.pi * target_sem / 3)
    
    # Create prediction feature set
    X_pred = pd.DataFrame({
        'time_trend': [next_time_trend],
        'semester_sin': [next_year_sin],
        'semester_cos': [next_year_cos],
        'year': [target_year]
    })
    
    return X_pred

# Load historical data
conn = mysql.connector.connect(**DB_CONFIG)
df_hist = pd.read_sql("""
    SELECT program_id, academic_year, semester, male, female, 
           (male + female) as total
    FROM enrollments 
    WHERE academic_year NOT LIKE '%-2027'  -- Exclude any existing 2026-2027 predictions
    ORDER BY program_id, academic_year, semester
""", conn)
conn.close()

if df_hist.empty:
    print("❌ No historical enrollment data found!")
    exit(1)

feature_cols = ['time_trend', 'semester_sin', 'semester_cos', 'year']
predictions = []
prog_names = {
    1: 'BA Communication', 2: 'BA English', 3: 'BA PolSci', 4: 'BLIS',
    5: 'BM Music', 6: 'BS Biology', 7: 'BSIT', 8: 'BS Social Work'
}

print(f"📊 Historical data points: {len(df_hist)}")
print(f"📚 Programs: {df_hist['program_id'].nunique()}\n")

# Train and predict for each program
for program_id in sorted(df_hist['program_id'].unique()):
    try:
        prog_hist = df_hist[df_hist['program_id'] == program_id].copy()
        
        if len(prog_hist) < 3:
            print(f"⚠️  Program {program_id}: insufficient history ({len(prog_hist)} points)")
            continue
        
        model_path = f'xgboost_prog_{program_id}.pkl'
        if not Path(model_path).exists():
            print(f"⚠️  Program {program_id}: no trained model found")
            continue
        
        # Load model
        model = joblib.load(model_path)
        
        # Generate features for 2026-2027, Semester 1
        X_next = create_features_for_prediction(prog_hist, target_year=2026, target_sem=1)
        
        # Predict total enrollment
        pred_total = int(model.predict(X_next)[0])
        pred_total = max(pred_total, 0)  # Ensure non-negative
        
        # Calculate male/female ratio from historical data
        avg_male_ratio = prog_hist['male'].sum() / prog_hist['total'].sum()
        avg_female_ratio = 1 - avg_male_ratio
        
        pred_male = int(pred_total * avg_male_ratio)
        pred_female = pred_total - pred_male
        
        predictions.append({
            'program_id': program_id,
            'academic_year': '2026-2027',
            'semester': 1,
            'predicted_total': pred_total,
            'predicted_male': pred_male,
            'predicted_female': pred_female,
            'confidence': 0.85
        })
        
        print(f"✅ {prog_names.get(program_id, f'Prog{program_id}'):20} → {pred_total:3d} total "
              f"({pred_male:3d}M / {pred_female:3d}F)")
        
    except Exception as e:
        print(f"❌ Program {program_id}: {str(e)}")

# Save predictions to database
if predictions:
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor()
    
    # Clear any existing 2026-2027 predictions
    cursor.execute("DELETE FROM predictions WHERE academic_year = '2026-2027'")
    conn.commit()
    
    # Insert new predictions
    for pred in predictions:
        cursor.execute("""
            INSERT INTO predictions 
            (program_id, academic_year, semester, predicted_total, predicted_male, predicted_female, confidence)
            VALUES (%s, %s, %s, %s, %s, %s, %s)
        """, (
            int(pred['program_id']),
            pred['academic_year'],
            int(pred['semester']),
            int(pred['predicted_total']),
            int(pred['predicted_male']),
            int(pred['predicted_female']),
            float(pred['confidence'])
        ))

    conn.commit()
    cursor.close()
    conn.close()
    
    print(f"\n🎉 Successfully saved {len(predictions)} predictions to database!")
    print(f"📅 Academic Year: 2026-2027 (Semester 1 only)")
else:
    print("❌ No predictions generated!")

print("\n🌐 Dashboard: http://localhost/enrollment-tracker/dashboard.php?login=1")