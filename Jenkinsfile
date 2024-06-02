pipeline {
    agent any
    environment {
	        DB_HOST = 'localhost'
	        DB_PORT = '3306'
	        DB_DATABASE = 'api-tasks'
	        DB_USERNAME = 'root'
	        DB_PASSWORD = ''
	        APP_KEY = 'base64:gBT1fcrtAChvEkzhYMzE5EWcrYWYJNYBuoZ27p/fYHY='
    	}
    stages {
       
        stage('Acceder a la Carpeta del Proyecto') {
            steps {
                dir('api-tareas') {
                    // Aquí se ejecutan los comandos específicos del proyecto Laravel
                    echo 'Entro a la carpeta'
		    bat '''composer install'''
                    bat '''php artisan migrate --env=testing'''
                    bat '''php artisan test'''
                }
            }
        }
    }
}
