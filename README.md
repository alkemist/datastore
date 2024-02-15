### Install

### Installation de PHP et ses extensions
`sudo apt-get install -y --no-install-recommends php8.1 
php8.1-cli php8.1-common php8.1-mysql php8.1-zip php8.1-gd php8.1-mbstring php8.1-curl php8.1-xml php8.1-intl php8.1-pgsql`

### Changer de version de PHP 
`sudo update-alternatives --config php`  

### Installation de composer
`php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"`  
`php -r "if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"`  
`php composer-setup.php`  
`php -r "unlink('composer-setup.php');"`  
`sudo mv composer.phar /usr/local/bin/composer`  

### Installation de symfony
`curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | sudo -E bas`
`sudo apt install symfony-cli`

### Installation de Node, NPM et Yarn
`curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash - && sudo apt-get install -y nodejs`   
`npm install --global yarn`

### Installation de postgres
`sudo apt install postgresql postgresql-contrib`  
`sudo systemctl start postgresql.service`

### Fix permissions
`HTTPDUSER=$(ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1)`  
`sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX var`  
`sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX var`  

### Installation du projet
`git clone`
`sudo chmod -R jaden:www-data`
`composer install`  
`yarn install`  
`yarn encore dev`    

### Initialisation du projet
`cp .env .env.local & nano .env.local` # update DATABASE_URL
`symfony console do:da:cr`   
`symfony console do:sc:up --force --complete`  
`psql -h localhost -U postgres -W -d resume -f resume-2023-05-01-01-12-58`


### Lancement du projet
`symfony server:start`  
`yarn encore dev`  