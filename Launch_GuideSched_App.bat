@echo off
title GuideSched App Launcher
echo Launching GuideSched Mobile App...

if exist "C:\Program Files\Google\Chrome\Application\chrome.exe" (
    start "" "C:\Program Files\Google\Chrome\Application\chrome.exe" --app="http://localhost/APPOINTMENT%%20IN%%20GUIDANCE%%20APP/app/" --window-size=430,920
    exit /b
)

if exist "C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe" (
    start "" "C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe" --app="http://localhost/APPOINTMENT%%20IN%%20GUIDANCE%%20APP/app/" --window-size=430,920
    exit /b
)

start http://localhost/APPOINTMENT%%20IN%%20GUIDANCE%%20APP/app/
