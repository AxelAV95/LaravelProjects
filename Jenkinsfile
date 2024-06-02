pipeline {
    agent any
    stages {
       
        stage('Acceder a la Carpeta del Proyecto') {
            steps {
                dir('api-tareas') {
                    // Aquí se ejecutan los comandos específicos del proyecto Laravel
                    echo 'Entro a la carpeta'
		    bat 'composer install'
                    bat 'php artisan migrate --env=testing'
                    bat 'php artisan test
                }
            }
        }
    }
}
