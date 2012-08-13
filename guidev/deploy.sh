#!/bin/bash
sencha create jsb -a index.html -p app.jsb3
sencha build -p app.jsb3 -d .
cp extjs/ext.js ../resources/js/extjs/ext.js
cp extjs/resources/css/ext-all.css ../resources/css/extjs/ext-all.css
cp app-all.js ../resources/js/extjs/app-all.js
cp -R resources/* ../resources/
