@echo off
echo Iniciando ambiente de desenvolvimento local para o Pix Credit Nexus...

REM Verificar se o WAMP está rodando
echo Verificando se o WAMP está rodando...
tasklist /FI "IMAGENAME eq wampmanager.exe" 2>NUL | find /I /N "wampmanager.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo WAMP está rodando.
) else (
    echo WAMP não está rodando. Iniciando WAMP...
    start "" "C:\wamp\wampmanager.exe"
    echo Aguardando WAMP iniciar...
    timeout /t 10
)

REM Iniciar o frontend
echo Iniciando o frontend na porta 8081...
cd /d %~dp0
npm run dev

echo.
echo Para acessar a aplicação, abra o seguinte URL:
echo http://localhost:8081
echo.
echo Pressione Ctrl+C para parar o servidor de desenvolvimento.
