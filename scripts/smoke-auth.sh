#!/bin/bash
BASE="http://localhost:8000"
COOKIE="/tmp/motorprime_cookies.txt"
rm -f $COOKIE

echo "[1] login.php sem palavra funcionario"
curl -s "$BASE/login.php" | grep -qi "funcionario" && echo "FAIL" || echo "OK"

echo "[2] index.php sem palavra funcionario"
curl -s "$BASE/index.php" | grep -qi "funcionario" && echo "FAIL" || echo "OK"

echo "[3] csrf no login"
curl -s -c $COOKIE -b $COOKIE -X POST "$BASE/login.php" -d "identificador=test@test.com&senha=12345678" -o /dev/null -w "%{http_code}\n"

echo "[4] login invalido"
TOKEN=$(curl -s -c $COOKIE -b $COOKIE "$BASE/login.php" | grep -oP 'name="csrf_token" value="\K[^"]+' | head -1)
curl -s -c $COOKIE -b $COOKIE -X POST "$BASE/login.php" -d "csrf_token=$TOKEN&login=1&identificador=naoexiste@x.com&senha=errada123" | grep -q "inválidos" && echo "OK" || echo "CHECK"

echo "[5] GET logout nao desloga"
curl -s -c $COOKIE -b $COOKIE "$BASE/logout.php" -o /dev/null -w "%{http_code}\n"
