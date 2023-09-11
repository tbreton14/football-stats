FROM php:8.2-apache

# Install / maj des différents paquets
RUN apt-get update || apt-get upgrade
RUN apt-get install -y gnupg2 \
    libicu-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    nano unzip wget curl \
    # pour wkhtmltopdf
    libxrender1 libfontconfig1


# install git, faire attention à mettre à jour le fichier git-completion.bash si la version de git change
RUN apt-get install -y git bash-completion git-flow \
    && curl -sS https://raw.githubusercontent.com/git/git/v2.11.0/contrib/completion/git-completion.bash  > ~/.git-completion.bash \
    && curl -sS https://raw.githubusercontent.com/git/git/v2.11.0/contrib/completion/git-prompt.sh  > ~/.git-prompt.sh \
    && curl -sS https://raw.githubusercontent.com/petervanderdoes/git-flow-completion/master/git-flow-completion.bash  > ~/.git-flow-completion.bash \
    && echo "\nsource ~/.git-prompt.sh\nsource ~/.git-completion.bash\nsource ~/.git-flow-completion.bash\n" >> ~/.bashrc

# install symfony-cli
#RUN wget https://get.symfony.com/cli/installer -O - | bash && mv ~/.symfony/bin/symfony /usr/local/bin/symfony

# install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer && chmod +x /usr/local/bin/composer && php -r "unlink('composer-setup.php');"

# install nodejs
RUN curl -sL https://deb.nodesource.com/setup_lts.x | bash - && apt-get install -y nodejs

# Install yarn
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - \
    && echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list \
    && apt-get update && apt-get install yarn

# install gulp
RUN npm install -g gulp

# Config du serveur
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
COPY ./custom.ini "$PHP_INI_DIR//conf.d/"
COPY ./opcache.ini "$PHP_INI_DIR//conf.d/"
COPY ./000-default.conf "/etc/apache2/sites-enabled/"
RUN cp /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled/ && \
    cp /etc/apache2/mods-available/headers.load /etc/apache2/mods-enabled/

# configuration de gd
RUN docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/

# install php extension
RUN pecl install apcu && docker-php-ext-enable apcu && docker-php-ext-install -j$(nproc) intl pdo_mysql gd zip bcmath soap opcache exif

# personalisation du prompt, [Docker]user@localhost:/chemin/en/cours (branche git en cours)
RUN echo "\n\nexport PS1='\[\e[38;5;39m\][Docker]\[\033[00m\]\u@\h:\w\[\033[0;33m\]\$(__git_ps1)\[\033[00m\] \\n\[\e[38;5;39m\]\\$\[\033[00m\] '" >> ~/.bashrc
