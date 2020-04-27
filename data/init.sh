#!/usr/bin/env bash

basePath=$(cd `dirname $0`; pwd)
testsPath=`dirname ${basePath}`

cp ${basePath}"/_gitignore" ${testsPath}"/.gitignore"
cp -rf ${basePath}"/api" ${testsPath}"/"

rm -rf ${testsPath}"/data"