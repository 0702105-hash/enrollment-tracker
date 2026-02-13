#!/usr/bin/env python3
import pandas as pd
import numpy as np
import mysql.connector
from pathlib import Path

print("🎓 CAS Enrollment Tracker - BULLETPROOF IMPORT")
print("📊 Processing your enrollment-tracker.xlsx...")

# Fix column names (handles "Semester " spaces)
df = pd.read_excel('../uploads/enrollment-tracker.xlsx')
df.columns = [col.strip() for col in df.columns]
print("✅ Columns:", list(df.columns))

DB_CONFIG = {'host': 'localhost', 'user': 'root', 'password': '', 'database': 'casDB'}

# Program mapping (matches YOUR casPrograms table)
prog_map = {
    'BACHELOR OF ARTS IN COMMUNICATION': 1,
    'BACHELOR OF ARTS IN ENGLISH LANGUAGE': 2,
    'BACHELOR OF ARTS IN POLITICAL SCIENCE': 3,
    'BACHELOR OF LIBRARY AND INFORMATION SCIENCE': 4,
    'BACHELOR OF MUSIC IN MUSIC EDUCATION': 5,
    'BACHELOR OF SCIENCE IN BIOLOGY': 6,
    'BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY': 7,
    'BACHELOR OF SCIENCE IN SOCIAL WORK': 8
}

sem_map = {'First': 1, 'Second': 2, 'Summer': 3}

# NEW CONNECTION - Separate cursor for each operation (FIXES sync error)
def safe_execute(sql, params=None):
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor()
    try:
        cursor.execute(sql, params or [])
        conn.commit()
        return True
    except Exception as e:
        print(f"❌ SQL Error: {e}")
        return False
    finally:
        cursor.close()
        conn.close()

# 1. Clear data (separate connection)
print("🗑️ Clearing old data...")
safe_execute("TRUNCATE TABLE enrollments")
safe_execute("TRUNCATE TABLE predictions")

# 2. Import YOUR data
imported = 0
conn = mysql.connector.connect(**DB_CONFIG)
cursor = conn.cursor()

for idx, row in df.iterrows():
    try:
        prog_name = str(row['Program']).upper().strip()
        prog_id = prog_map.get(prog_name, 1)
        sem_text = str(row['Semester']).strip()
        semester = sem_map.get(sem_text, 1)
        male = max(0, int(row['Male']) if pd.notna(row['Male']) else 0)
        female = max(0, int(row['Female']) if pd.notna(row['Female']) else 0)
        
        cursor.execute("""
            INSERT INTO enrollments (program_id, academic_year, semester, male, female)
            VALUES (%s, %s, %s, %s, %s)
            ON DUPLICATE KEY UPDATE 
                male = VALUES(male), 
                female = VALUES(female)
        """, (prog_id, str(row['AcademicYear']).strip(), semester, int(male), int(female)))
        imported += cursor.rowcount
    except Exception as e:
        print(f"⚠️ Row {idx}: {e}")
        continue

conn.commit()
print(f"✅ IMPORTED {imported} records!")

# 3. Generate predictions (NEW connection)
cursor.execute("""
    SELECT program_id, (male + female) as total 
    FROM enrollments 
    GROUP BY program_id
    ORDER BY program_id
""")
totals_by_program = {}
for (pid, total) in cursor.fetchall():
    totals_by_program.setdefault(pid, []).append(total)

cursor.close()
conn.close()

# Predictions (separate connection)
for pid, totals in totals_by_program.items():
    if len(totals) >= 2:
        times = list(range(len(totals)))
        n, x, y = len(times), np.array(times), np.array(totals)
        slope = (n * np.sum(x*y) - np.sum(x)*np.sum(y)) / (n*np.sum(x*x) - np.sum(x)**2)
        pred = slope * n + (np.sum(y) - slope*np.sum(x)) / n
        
        safe_execute("""
            INSERT INTO predictions (program_id, academic_year, semester, predicted_total)
            VALUES (%s, '2026-2027', 1, %s)
        """, (pid, int(pred)))

prog_names = {
    1: 'BaComm', 2: 'English', 3: 'PolSci', 4: 'Library', 
    5: 'Music', 6: 'Biology', 7: 'BSIT', 8: 'SocialWork'
}

print("\n🔮 PREDICTIONS:")
for pid, totals in totals_by_program.items():
    if len(totals) >= 2:
        pred = slope * len(totals) + (np.sum(totals) - slope*np.sum(list(range(len(totals)))) / len(totals))
        print(f"   {prog_names.get(pid, pid)}: {int(pred)} students")

print("\n🎉 COMPLETE! http://localhost/enrollment-tracker/dashboard.php")
print("👉 Login: admin / admin123")
