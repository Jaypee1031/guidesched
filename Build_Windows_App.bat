@echo off
setlocal enabledelayedexpansion
title GuideSched - Windows Desktop Builder
color 0B

echo =======================================================================
echo         GUIDESCHED - WINDOWS NATIVE .EXE BUILD PIPELINE
echo       Cagasat High School Guidance Counseling System
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

:: 2. Check Windows Developer Mode
echo [*] Checking Windows Developer Mode...
reg query "HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Windows\CurrentVersion\AppModelUnlock" /v "AllowDevelopmentWithoutDevLicense" 2>nul | find "0x1" >nul
if %ERRORLEVEL% neq 0 (
    echo [!] Windows Developer Mode is NOT enabled.
    echo Flutter Windows desktop requires Developer Mode to create symlinks.
    echo.
    echo Opening Windows Developer Settings now...
    start ms-settings:developers
    echo Please toggle 'Developer Mode' to ON in the window that opened.
    echo.
    set /p CONTINUE="Once Developer Mode is enabled, press Enter to continue (or N to abort): "
    if /i "!CONTINUE!"=="N" goto :error
)
echo [OK] Developer Mode verified.

:: 3. Check Visual Studio C++
echo [*] Checking Visual Studio 2022 C++ build tools...
call flutter doctor | find "Visual Studio - develop Windows apps" | find "[X]" >nul
if %ERRORLEVEL% equ 0 (
    echo [!] Visual Studio C++ is NOT installed.
    echo Building Windows apps requires Visual Studio 2022 with "Desktop development with C++".
    echo.
    echo You can install it using Winget:
    echo     winget install Microsoft.VisualStudio.2022.Community --override "--add Microsoft.VisualStudio.Workload.VCTools --includeRecommended --passive"
    echo.
    set /p INSTALL_VS="Would you like to start installing Visual Studio Community now? (Y/N): "
    if /i "!INSTALL_VS!"=="Y" (
        echo [*] Launching Visual Studio 2022 installer via winget (this downloads several GBs)...
        winget install Microsoft.VisualStudio.2022.Community --override "--add Microsoft.VisualStudio.Workload.VCTools --includeRecommended --passive"
        echo [*] Once installation completes, restart this script to compile the .exe.
        goto :end
    ) else (
        goto :error
    )
)
echo [OK] Visual Studio C++ toolchain found.

:: 4. Build the Release Windows Executable
echo.
echo =======================================================================
echo [*] Fetching dependencies...
cd /d "%~dp0guidesched_app"
call flutter pub get
if %ERRORLEVEL% neq 0 goto :error

echo.
echo [*] Compiling Native Windows Release Executable (.exe)...
call flutter build windows --release
if %ERRORLEVEL% neq 0 goto :error

:: 5. Copy output to dist folder
if not exist "%~dp0dist\windows_app" mkdir "%~dp0dist\windows_app"
xcopy /s /y /q "%~dp0guidesched_app\build\windows\x64\runner\Release\*" "%~dp0dist\windows_app\" >nul 2>&1

echo.
echo =======================================================================
echo [SUCCESS] Windows native app compiled successfully!
echo.
echo Output location:
echo   %~dp0dist\windows_app\guidesched_app.exe
echo.
echo You can run guidesched_app.exe directly from that folder!
echo =======================================================================
explorer "%~dp0dist\windows_app"
goto :end

:error
echo.
echo [!] Build aborted due to missing dependencies or configuration.
echo Please review the instructions above.

:end
echo.
pause
