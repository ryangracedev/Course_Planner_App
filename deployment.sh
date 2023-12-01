# this bash script will copy and process all relivant files into public. for deployment
if [ -d "deployment" ]; then
  rm -r deployment
fi

mkdir deployment
cp Source/src/*.php deployment

if [ -d "deployment/templates" ]; then
  rm -r deployment/templates
fi
mkdir deployment/templates/
cp Source/src/templates/*.php deployment/templates/

if [ -d "deployment/rest" ]; then
  rm -r deployment/rest
fi
mkdir deployment/rest/
cp Source/src/rest/*.php deployment/rest/
cp Source/src/*.html deployment
cp Source/src/*.js deployment
cp -r Source/public/assets deployment/assets
cp -r Source/public/styles deployment/styles
cp Source/public/jquery-3.7.1.min.js deployment/jquery-3.7.1.min.js
mv deployment/assets/favicon.ico deployment/favicon.ico
