#!/bin/bash


cd `dirname $0/`

frontend_dir=`pwd`

echo 'start process build front end'

npm run build

echo 'done build front end. Start copy process'


rm -rf $frontend_dir/../backend/public/adm
mkdir $frontend_dir/../backend/public/adm

cp -r dist/css/ $frontend_dir/../backend/public/adm/css
cp -r dist/fonts/ $frontend_dir/../backend/public/adm/fonts
cp -r dist/img/ $frontend_dir/../backend/public/adm/img
cp -r dist/js/ $frontend_dir/../backend/public/adm/js

cp dist/favicon.ico $frontend_dir/../backend/public/adm/
cp dist/loader.css $frontend_dir/../backend/public/adm/
cp dist/logo.png $frontend_dir/../backend/public/adm/
cp dist/index.html $frontend_dir/../backend/public/adm

echo 'done all'

