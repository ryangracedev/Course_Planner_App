if exist deployment (rmdir /s /q deployment)
mkdir deployment
xcopy ".\Source\src\*.*"  "deployment"
mkdir deployment\templates
xcopy ".\Source\src\templates"  "deployment\templates" /E/H

mkdir deployment\rest
xcopy ".\Source\src\rest"  "deployment\rest" /E/H

xcopy ".\Source\public\"  "deployment" /E/H
move ".\deployment\assets\favicon.ico" "deployment\favicon.ico"
