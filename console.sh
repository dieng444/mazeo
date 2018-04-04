#!/bin/bash

cp="create:package"
cc="create:controller"
ce="create:entity"
cm="create:manager"
cf="create:form"
h="help"

# Display helper message
displayHelpMessage() {
    echo " "
    echo " Welcome to Mazeo framework processing tool helper\n"
    echo " "$h"\n\n\tShows helper message\n"
    echo " "$cp"\n\n\tAllows to create a new package (eg : create:package Acme:User)\n"
    echo " "$cc"\n\n\tAllows to create a new controller (eg : create:controller Acme:User:Profile)\n"
    echo " "$ce"\n\n\tAllows to create a new entity in the specified package (eg : create:entity Acme:User:Profile)\n"
    echo " "$cm"\n\n\tAllows to create a new manager in the specified package (eg : create:manager Acme:User:Profile)\n"
    echo " "$cf"\n\n\tAllows to create a new form in the specified package (eg : create:form Acme:User:Profile)\n"
}

# Create new package structure
createPackageStructure () {
    package=$1
    if [ -n $package ]; then
        app=$(echo $package | awk -F':' '{print $1}')
        package=$(echo $package | awk -F':' '{print $2}')
        path="src/$app"
        lowerPackage=$(echo $package | awk '{print tolower($0)}')
        if [ -d $path ]; then
            mkdir "$path/$package" && cd "$path/$package" && mkdir Controller Entity Manager Form Resources &&
            touch Controller"/$package"Controller.php && touch  Entity"/$package".php &&  touch Manager"/$package"Manager.php &&
            touch Form"/$package"Form.php && cd Resources && mkdir config public views && touch config/routing.yml &&
            mkdir config/form && touch config/form/"$lowerPackage".yml && touch views/layout.twig && mkdir views/"$package" &&
            cd public && mkdir img js css fonts
            echo " "
            echo " You can now start using the new package at this name $package"
            echo " "
        fi
    fi
}

# Create new entity properties
createEntityProperties() {
    file=$1
    namespace=$2
    entityType=$3
    entity=$4
    echo "<?php\t\n\nnamespace $namespace;\n\nuse Mazeo\\Main\\$entityType\\$entityType;\n\n/**\n* class\t$entity\n* @package\t$namespace\n**/\nclass $entity extends $entityType {\n" >> $file 2>&1
    echo " "
    echo " You can now start creating your $entityType properties and type blank \"enter\" to done"
    echo " "
    while [ -n $attribute ];
    do
        read -p " Enter attribute name ": attribute
        echo " "
        if [ -n $attribute ]; then
            if [ -z $attribute ]; then
                echo "You can now start generating getters and setters by typing the same attributes names."
                echo " "
                while [ -n $funcAttr ];
                    do
                        read -p " Enter attribute name ": funcAttr
                        echo " "
                        if [ -n $funcAttr ]; then
                            if [ -z $funcAttr ]; then
                                echo "\t/**\n\t* $entity constructor\n\t* @param array \$data\n\t*/\n\tpublic function __construct(array \$data=array()) \n\t{\n\t\tparent::__construct(\$data);\n\t}\n}">> $file 2>&1
                                echo " "
                                echo " You can now start using the new Entity class at this namespace $namespace\\$entity"
                                echo " "
                                exit 1
                            fi
                            funcName=`echo "$(echo $funcAttr | sed 's/.*/\u&/')"`;
                            echo "\t/**\n\t* @param \$$funcAttr\n\t*/\n\tpublic function set"$funcName"(\$$funcAttr)\n\t{\n\t\t\$this->$funcAttr = \$$funcAttr;\n\t\treturn \$this;\n\t}\n" >> $file 2>&1
                            echo "\t/**\n\t* @param \$$funcAttr\n\t*/\n\tpublic function get$funcName()\n\t{\n\t\treturn \$this->$funcAttr;\n\t}\n" >> $file 2>&1
                        fi
                done
            fi
            echo "\t/**\n\t* @var \$$attribute\n\t*/\n\tprivate\t$"$attribute";\n" >> $file 2>&1
        fi
    done
}

# create new manager properties
createManagerProperties() {
    file=$1
    namespace=$2
    entityType=$3
    manager=$4
    echo " "
    echo " You can now start creating the manager \"$manager\" properties"
    echo " "
    while [ -z $tableName ]; do #white space is taken into account
       read -p " Enter the associated table name ": tableName
    done
    echo " "
    while [ -z $id ]; do
       read -p " Enter the associated table ID name ": id
    done
    echo " "
    echo " Enter \"false\" if you does not want to active exception or tape just enter in the other case"
    echo " "
    read -p " Do you want to specify an columns exception [true] ? ": exception
    echo " "
    if [ -z $exception ]; then
        exception="true"
        read -p " Enter the exception token[_-]": token
    fi
    if [ -n $assocEntity ] && [ -n $assocEntityNamespace ] && [ -n $tableName ] && [ -n $id ]; then
        echo "<?php\t\n\nnamespace $namespace;\n\nuse Mazeo\\Main\\$entityType\\$entityType;\n\n/**\n* class\t$manager\n* @package\t$namespace\n**/\nclass $manager extends $entityType {\n" >> $file 2>&1
        echo "\t/**\n\t* @var string TABLE_NAME\n\t*/\n\tconst TABLE_NAME = '$tableName';\n" >> $file 2>&1
        echo "\t/**\n\t* @var string ID\n\t*/\n\tconst ID = 'id';\n" >> $file 2>&1
        echo "\t/**\n\t* @var array COLUMN_EXCEPTION\n\t*/\n\tconst COLUMN_EXCEPTION = array($exception,'$token');\n}" >> $file 2>&1
    fi
    echo " "
    echo " You can now start using the new Manager class at this namespace $namespace\\$manager"
    echo " "
}

#Create new form properties
createFormProperties() {
    file=$1
    namespace=$2
    entityType=$3
    form=$4
    echo " "
    echo " You can now start creating the form \"$form\" properties"
    echo " "
    while [ -z $assocEntityNamespace ]; do #white space is taken into account
        echo " Note that the namespace separator is not \"\\\" here but \":\""
        echo " "
        read -p " Enter the associated entity namespace ": assocEntityNamespace
        if [ -n $assocEntityNamespace ]; then
            app=$(echo $assocEntityNamespace | awk -F':' '{print $1}')
            package=$(echo $assocEntityNamespace | awk -F':' '{print $2}')
            component=$(echo $assocEntityNamespace | awk -F':' '{print $3}')
            entity=$(echo $assocEntityNamespace | awk -F':' '{print $4}')
            lowerEntity=$(echo $entity | awk '{print tolower($0)}')
            path="src/$app/$package/$component/$entity.php"
            assocEntityNamespace=$app"\\"$package"\\"$component"\\"$entity
            if [ -f $path ]; then
               if [ -n $assocEntity ]; then
                    echo "<?php\t\n\nnamespace $namespace;\n\nuse Mazeo\\Main\\$entityType\\$entityType;\n\nuse $assocEntityNamespace;\n\n/**\n* class\t$form\n* @package\t$namespace\n**/\nclass $form extends $entityType {\n" >> $file 2>&1
                    echo "\t/**\n\t* $form constructor\n\t* @param $entity \$$lowerEntity\n\t*/\n\tpublic function __construct($entity \$$lowerEntity) \n\t{\n\t\tparent::__construct(\$$lowerEntity);\n\t}\n}">> $file 2>&1
                    echo " "
                    echo " You can now start using the new Form class at this namespace $namespace\\$form"
                    echo " "
                    exit 1
                fi
            else
                echo " "
                echo " The specified namespace does not exist"
                echo " "
            fi
        fi
    done
}
#Create new form properties
createController() {
    file=$1
    namespace=$2
    entityType=$3
    controller=$4
    lowerEntity=$(echo $controller | awk '{print tolower($0)}')
    echo "<?php\t\n\nnamespace $namespace;\n\nuse Mazeo\\Main\\$entityType\\$entityType;\n/**\n* class\t$controller\n* @package\t$namespace\n**/\nclass $controller extends $entityType {\n" >> $file 2>&1
    echo "\t/**\n\t* $controller constructor\n\t* @param $entity \$$lowerEntity\n\t*/\n\tpublic function __construct() \n\t{\n\t\tparent::__construct();\n\t}\n}">> $file 2>&1
    echo " "
    echo " You can now start using the new Controller class at this namespace $namespace\\$controller"
    echo " "
    exit 1
}

#create new class
createClass () {
    entity=$1
    entityType=$2
    app=$(echo $entity | awk -F':' '{print $1}')
    package=$(echo $entity | awk -F':' '{print $2}')
    entity=$(echo $entity | awk -F':' '{print $3}')
    path="src/$app/$package/$entityType/"
    namespace=$app"\\"$package"\\$entityType"
    lowerPackage=$(echo $package | awk '{print tolower($0)}')
    appPath="src/$app/"
    packagePath="src/$app/$package"
    if [ -d $appPath ]; then
        if [ -d $packagePath ]; then
            if [ -d $path ]; then
                file=$path"/"$entity".php"
                if [ $entityType = "Entity" ]; then
                    createEntityProperties $file $namespace $entityType $entity
                elif [ $entityType = "Manager" ]; then
                    createManagerProperties $file $namespace $entityType $entity
                elif [ $entityType = "Form" ]; then
                    createFormProperties $file $namespace $entityType $entity
                elif [ $entityType = "Controller" ]; then
                    createController $file $namespace $entityType $entity
                fi
            fi
        else
            echo "The package \"$package\" does not exists..."
        fi
    else
        echo "The app \"$app\" does not exists..."
    fi
}

#Create new entity class
createEntityClass () {
    createClass $1 Entity
}

#Create new manager class
createManagerClass () {
    createClass $1 Manager
}

#Create new form class
createFormClass() {
    createClass $1 Form
}

#Create new controller class
createControllerClass() {
    createClass $1 Controller
}

if [ $# -lt 1 ]; then
    echo " You must specify the parameter to execute \nexit code 1"
    exit 1
fi
if [ $1 = $cp ] || [ $1 = $ce ] || [ $1 = $cm ] || [ $1 = $cf ] || [ $1 = $cc ]; then
    if [ -z $2 ]; then
        echo " You are executing command $1 without the name parameter"
        if [ $1 = $cp ]; then
            read -p " Enter the package name ": package
            if [ -n $package ]; then
                createPackageStructure $package
            fi
        elif [ $1 = $ce ]; then
            read -p " Enter the entity namespace  ": entity
            if [ -n $entity ]; then
                createEntityClass $entity
            fi
        elif [ $1 = $cm ]; then
            read -p " Enter the manager namespace  ": manager
            if [ -n $manager ]; then
                createManagerClass $manager
            fi
        elif [ $1 = $cf ]; then
            read -p " Enter the form namespace  ": form
            if [ -n $form ]; then
                createFormClass $form
            fi
        elif [ $1 = $cc ]; then
            read -p " Enter the controller namespace  ": controller
            if [ -n $controller ]; then
                createControllerClass $controller
            fi
        fi
    else
        if [ $1 = $cp ]; then
            createPackageStructure $2
        elif [ $1 = $ce ]; then
            createEntityClass $2
        elif [ $1 = $cm ]; then
            createManagerClass $2
        elif [ $1 = $cf ]; then
            createFormClass $2
        elif [ $1 = $cc ]; then
            createControllerClass $2
        fi
    fi
elif [ $1 = $h ]; then
    displayHelpMessage
fi
