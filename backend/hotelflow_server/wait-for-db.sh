#!/bin/sh
host="$1"
user="$2"
password="$3"
shift 3
cmd="$@"

until mysql -h "$host" -u"$user" -p"$password" -e "select 1" > /dev/null 2>&1; do
  echo "Waiting for MySQL at $host..."
  sleep 2
done

exec $cmd
