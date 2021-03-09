REM Makes the output non-verbose
@echo off
REM Save all arguments
set RESTVAR=
shift
:loop1
if "%1"=="" goto after_loop
set RESTVAR=%RESTVAR% %1
shift
goto loop1

:after_loop
php -r "require_once 'console.php';" %RESTVAR%