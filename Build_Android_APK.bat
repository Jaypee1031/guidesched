@echo off
setlocal enabledelayedexpansion
title GuideSched - Android APK Builder
color 0A

echo =======================================================================
echo          GUIDESCHED - ANDROID APK RELEASE BUILD PIPELINE
echo       Cagasat High School Guidance Counseling Mobile System
echo =======================================================================
echo.

:: 1. Ensure Flutter is on PATH
where flutter >nul 2>&1
if %ERRORLEVEL% neq 0 (
    if exist "C:\flutter\bin\flutter.bat" (
        echo [*] Flutter located at C:\flutter\bin. Setting temporary PATH...
        set "PATH=%PATH%;C:\flutter\bin"
    ) else (
        echo [ERROR] Flutter SDK not found! Please ensure C:\flutter\bin exists.
        goto :error
    )
)

:: 2. Check Java JDK
echo [*] Checking Java (JDK)...
where java >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo [!] Java (JDK 17) is NOT detected on this system.
    echo.
    echo To build Android APKs, a Java Development Kit (JDK 17) is required.
    echo You can automatically install it via Winget:
    echo     winget install Microsoft.OpenJDK.17
    echo.
    set /p INSTALL_JDK="Would you like to install OpenJDK 17 now via winget? (Y/N): "
    if /i "!INSTALL_JDK!"=="Y" (
        echo [*] Installing Microsoft OpenJDK 17...
        winget install Microsoft.OpenJDK.17 --accept-package-agreements --accept-source-agreements
        echo [*] Please restart this script after the installation finishes.
        goto :end
    ) else (
        goto :error
    )
)
echo [OK] Java detected.

:: 3. Check Android SDK
echo [*] Checking Android SDK...
set "SDK_PATH="
if exist "%LOCALAPPDATA%\Android\Sdk" (
    set "SDK_PATH=%LOCALAPPDATA%\Android\Sdk"
) else if exist "C:\Android\Sdk" (
    set "SDK_PATH=C:\Android\Sdk"
)

if "!SDK_PATH!"=="" (
    echo [!] Android SDK was not found in default locations.
    echo.
    echo Android Studio is required to download and manage the Android SDK.
    echo You can install it via:
    echo     winget install Google.AndroidStudio
    echo.
    set /p INSTALL_AS="Would you like to install Android Studio now via winget? (Y/N): "
    if /i "!INSTALL_AS!"=="Y" (
        echo [*] Installing Android Studio...
        winget install Google.AndroidStudio --accept-package-agreements --accept-source-agreements
        echo.
        echo [*] Once installed, launch Android Studio once to complete the SDK setup wizard.
        echo [*] Make sure 'Android SDK Command-line Tools' is checked in SDK Manager.
        goto :end
    ) else (
        goto :error
    )
)

echo [OK] Found Android SDK at: !SDK_PATH!
call flutter config --android-sdk "!SDK_PATH!" >nul 2>&1

:: 4. Build the Release APK
echo.
echo =======================================================================
echo [*] Fetching dependencies...
cd /d "%~dp0guidesched_app"
call flutter pub get
if %ERRORLEVEL% neq 0 goto :error

echo.
echo [*] Compiling Release APK for Android (this may take a couple of minutes)...
call flutter build apk --release
if %ERRORLEVEL% neq 0 goto :error

:: 5. Copy output to dist folder
if not exist "%~dp0dist" mkdir "%~dp0dist"
copy /y "%~dp0guidesched_app\build\app\outputs\flutter-apk\app-release.apk" "%~dp0dist\GuideSched_v1.0.apk" >nul 2>&1

echo.
echo =======================================================================
echo [SUCCESS] APK compiled successfully!
echo.
echo Output location:
echo   %~dp0dist\GuideSched_v1.0.apk
echo.
echo You can copy this .apk file to any Android smartphone or tablet
echo and tap to install it!
echo =======================================================================
explorer "%~dp0dist"
goto :end

:error
echo.
echo [!] Build aborted due to missing dependencies or errors.
echo Please review the output above.

:end
echo.
pause
