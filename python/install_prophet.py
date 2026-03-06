#!/usr/bin/env python3
"""
Install and test Prophet with proper configuration
"""

import os
import sys
import subprocess

print("=" * 100)
print("Installing Prophet with proper configuration...")
print("=" * 100)

# Set environment variable
os.environ['STAN_BACKEND'] = 'CMDSTANPY'

# Step 1: Uninstall old versions
print("\n1️⃣  Uninstalling old Prophet versions...")
subprocess.run([sys.executable, "-m", "pip", "uninstall", "prophet", "-y"], 
               capture_output=True)
subprocess.run([sys.executable, "-m", "pip", "uninstall", "pystan", "-y"], 
               capture_output=True)

# Step 2: Install cmdstanpy
print("2️⃣  Installing cmdstanpy...")
subprocess.run([sys.executable, "-m", "pip", "install", "--upgrade", "cmdstanpy", 
                "--no-cache-dir"], check=True)

# Step 3: Install Prophet
print("3️⃣  Installing Prophet (this may take a few minutes)...")
subprocess.run([sys.executable, "-m", "pip", "install", "prophet", 
                "--no-cache-dir"], check=True)

# Step 4: Test Prophet
print("\n4️⃣  Testing Prophet import...")
try:
    from prophet import Prophet
    import pandas as pd
    
    # Create sample data
    df = pd.DataFrame({
        'ds': pd.date_range('2020-01-01', periods=10),
        'y': [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
    })
    
    # Try to fit
    print("   Creating Prophet model...")
    model = Prophet(stan_backend='cmdstanpy')
    
    print("   Fitting model (first run downloads Stan)...")
    import warnings
    with warnings.catch_warnings():
        warnings.simplefilter("ignore")
        model.fit(df)
    
    print("   Making predictions...")
    future = model.make_future_dataframe(periods=3)
    forecast = model.predict(future)
    
    print("\n✅ Prophet is working correctly!")
    print(f"   Sample forecast:\n{forecast[['ds', 'yhat']].tail()}")
    
except Exception as e:
    print(f"\n❌ Prophet test failed: {str(e)}")
    sys.exit(1)

print("\n" + "=" * 100)
print("✨ Prophet installation complete and verified!")
print("=" * 100)