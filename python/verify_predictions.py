#!/usr/bin/env python3
import mysql.connector

DB_CONFIG = {
    'host': 'localhost', 'user': 'root', 'password': '', 'database': 'casDB'
}

conn = mysql.connector.connect(**DB_CONFIG)
cursor = conn.cursor()

# Check predictions by year and semester
print("\n📊 PREDICTIONS BY YEAR AND SEMESTER:")
print("=" * 60)
cursor.execute("""
    SELECT academic_year, semester, COUNT(*) as count
    FROM predictions 
    WHERE academic_year IN ('2026-2027', '2027-2028')
    GROUP BY academic_year, semester 
    ORDER BY academic_year, semester
""")
rows = cursor.fetchall()
for row in rows:
    print(f"  {row[0]} Semester {row[1]}: {row[2]} records")

# Check total by year
print("\n📊 PREDICTIONS BY YEAR:")
print("=" * 60)
cursor.execute("""
    SELECT academic_year, COUNT(*) as count
    FROM predictions 
    WHERE academic_year IN ('2026-2027', '2027-2028')
    GROUP BY academic_year
    ORDER BY academic_year
""")
rows = cursor.fetchall()
for row in rows:
    print(f"  {row[0]}: {row[1]} records")

# Check by model
print("\n📊 PREDICTIONS BY MODEL:")
print("=" * 60)
cursor.execute("""
    SELECT model_name, COUNT(*) as count
    FROM predictions 
    WHERE academic_year IN ('2026-2027', '2027-2028')
    GROUP BY model_name
    ORDER BY model_name
""")
rows = cursor.fetchall()
for row in rows:
    print(f"  {row[0]}: {row[1]} records")

# Sample predictions
print("\n📋 SAMPLE PREDICTIONS (First 10):")
print("=" * 60)
cursor.execute("""
    SELECT program_id, academic_year, semester, predicted_total, model_name
    FROM predictions 
    WHERE academic_year IN ('2026-2027', '2027-2028')
    ORDER BY program_id, academic_year, semester, model_name
    LIMIT 10
""")
rows = cursor.fetchall()
print(f"{'Program':<12} {'Year':<15} {'Sem':<4} {'Total':<8} {'Model':<15}")
print("-" * 60)
for row in rows:
    print(f"  {row[0]:<12} {row[1]:<15} {row[2]:<4} {row[3]:<8} {row[4]:<15}")

cursor.close()
conn.close()
print("\n✅ Verification complete!")
