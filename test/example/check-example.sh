#!/usr/bin/env bash

cd "$(dirname ${BASH_SOURCE[0]})"
cd ../..

check () {
  OUT=`php $1`
  if [ "$OUT" != "$2" ]
  then
    echo "failed: $1"
    echo "expected: $2"
    echo "actual: $OUT"
    exit 1
  else
    echo "ok: $1"
  fi
}

check example/laminas-servicemanager.php $'Doctrine\\ORM\\EntityManager\nDoctrine\\ORM\\EntityManager\nDoctrine\\ORM\\EntityManager\nDoctrine\\ORM\\EntityManager\nDoctrine\\ORM\\EntityManager'
check example/illuminate-container.php $'Doctrine\\ORM\\EntityManager\nDoctrine\\ORM\\EntityManager\nDoctrine\\ORM\\EntityManager'
