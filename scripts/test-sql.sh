#!/usr/bin/env bash
# サーバ側ルール（仕様書 §25）の検証を、実際の Postgres に対して走らせる。
#
#   ./scripts/test-sql.sh
#
# Supabase 本体は不要。ローカルの Postgres 16 にスキーマと RPC を適用し、
# supabase/tests/*.sql を実行する。auth スキーマは最小限のスタブで代用する。
set -euo pipefail

PGBIN="${PGBIN:-/usr/lib/postgresql/16/bin}"
PGPORT="${PGPORT:-55432}"
PGDATA_DIR="${PGDATA_DIR:-/var/lib/postgresql/sessiontest}"
DB=sessiontest
PSQL="psql -h 127.0.0.1 -p $PGPORT -U postgres"

if ! $PSQL -c 'select 1' > /dev/null 2>&1; then
  echo "Postgres を $PGPORT で起動してください（PGDATA=$PGDATA_DIR）" >&2
  exit 1
fi

$PSQL -q -c "drop database if exists $DB;" -c "create database $DB;"
$PSQL -q -d $DB -c "create schema auth; create table auth.users (id uuid primary key);"
$PSQL -q -d $DB -c "create or replace function auth.uid() returns uuid language sql stable as \$\$ select nullif(current_setting('request.jwt.claim.sub', true), '')::uuid \$\$;"
$PSQL -q -d $DB -c "create role authenticated nologin;" 2>/dev/null || true

for f in supabase/migrations/*.sql; do
  # storage は Supabase 本体が持つスキーマなのでローカル検証では飛ばす
  case "$f" in *storage*) continue;; esac
  echo "applying $(basename "$f")"
  $PSQL -q -v ON_ERROR_STOP=1 -d $DB -f "$f"
done

$PSQL -q -d $DB -c "grant usage on schema public, auth to authenticated;
  grant select, insert, update on all tables in schema public to authenticated;
  grant select on auth.users to authenticated;
  grant execute on all functions in schema public to authenticated;
  revoke all on function apply_age_verification_result(text, boolean, text) from authenticated;"

for f in supabase/tests/*.sql; do
  echo "--- $(basename "$f") ---"
  $PSQL -q -v ON_ERROR_STOP=1 -d $DB -f "$f" 2>&1 | grep -E "PASS|FAIL|ERROR" || true
done
