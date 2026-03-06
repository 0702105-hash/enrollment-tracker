XGBoost prediction commands for Enrollment Tracker
===============================================

This file lists the commands and steps to import data, train the per-program
XGBoost models, and run predictions for the 2026-2027 academic year.

Prerequisites
-------------
- MySQL running and accessible (DB: `casDB`, user: `root`, password: ``)
- `uploads/enrollment-tracker.xlsx` present in the project's `uploads/` folder
- Virtual environment created at `./.venv` and Python packages installed

Usage (PowerShell, run from project root)
----------------------------------------
1) Activate the virtual environment

```powershell
.\.venv\Scripts\Activate.ps1
```

2) Install dependencies (if not already installed)

```powershell
.\.venv\Scripts\python.exe -m pip install -r python\requirements.txt
```

3) Import the Excel data into the `enrollments` table (optional but recommended)

```powershell
python python\import_excel.py
```

4) Train the XGBoost models (creates `xgboost_prog_{id}.pkl` files)

```powershell
python python\train_xgboost.py
```

5) Run predictions (writes to `predictions` table for academic year 2026-2027)

```powershell
python python\predict_xgboost.py
```

6) View the dashboard (web):

Open in your browser:
http://localhost/enrollment-tracker/dashboard.php?login=1

Notes and troubleshooting
-------------------------
- Database credentials are hard-coded in the scripts under `python/`.
  Edit `train_xgboost.py`, `predict_xgboost.py`, and `import_excel.py` DB_CONFIG
  if your MySQL user/password or database name differ.
- If you encounter the "Fatal error in launcher: Unable to create process"
  pip launcher error, use the venv Python to run pip (see step 2 above).
- If models already exist (`xgboost_prog_*.pkl`), you can skip step 4 and
  run only the prediction script (step 5).
- The prediction script currently generates predictions for Semester 1 of
  academic year 2026-2027 only.
- For `predict_multi_models.py`, make sure dependencies are refreshed after
  pulling latest changes:
  `python -m pip install -r python\\requirements.txt`

Advanced: Recreate venv `pip` wrappers
-------------------------------------
If the `pip.exe` launcher points to a moved Python and errors persist, recreate
the virtual environment or reinstall pip inside the venv:

```powershell
# from project root
Remove-Item -Recurse -Force .\.venv
python -m venv .venv
.\.venv\Scripts\Activate.ps1
python -m pip install --upgrade pip setuptools wheel
python -m pip install -r python\requirements.txt
```

Contact
-------
If anything fails, capture the terminal output and share it so we can
troubleshoot the specific error.

Happy predicting! 🚀
