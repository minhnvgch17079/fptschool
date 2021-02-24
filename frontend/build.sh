#!/bin/bash


cd `dirname $0/`

frontend_dir=`pwd`

echo 'start process build front end'

npm run build

echo 'done build front end. Start copy process'


rm -rf $frontend_dir/../backend/public/css
rm -rf $frontend_dir/../backend/public/fonts
rm -rf $frontend_dir/../backend/public/img
rm -rf $frontend_dir/../backend/public/js

rm -f $frontend_dir/../backend/public/favicon.ico
rm -f $frontend_dir/../backend/public/loader.css
rm -f $frontend_dir/../backend/public/logo.png
rm -f $frontend_dir/../backend/public/adm/index.html

mkdir $frontend_dir/../backend/public/adm


cp -r dist/css/ $frontend_dir/../backend/public/css
cp -r dist/fonts/ $frontend_dir/../backend/public/fonts
cp -r dist/img/ $frontend_dir/../backend/public/img
cp -r dist/js/ $frontend_dir/../backend/public/js

cp dist/favicon.ico $frontend_dir/../backend/public/
cp dist/loader.css $frontend_dir/../backend/public/
cp dist/logo.png $frontend_dir/../backend/public/
cp dist/index.html $frontend_dir/../backend/public/adm/

echo 'done all'

