# Base image
FROM php:8.4-apache

# Version variables
ENV GIT_VERSION="v2.11.0" \
    WKHTMLTOPDF_VERSION="0.12.6-3"

# Configure apt for better reliability
RUN echo 'Acquire::Retries "3";' > /etc/apt/apt.conf.d/80-retries \
    && echo "APT::Get::Assume-Yes \"true\";" > /etc/apt/apt.conf.d/90assumeyes \
    && echo "APT::Keep-Downloaded-Packages \"false\";" > /etc/apt/apt.conf.d/90cleaner

# System dependencies with retry mechanism
RUN --mount=type=cache,target=/var/cache/apt \
    for i in $(seq 1 3); do \
        apt-get update && break || sleep 1; \
    done \
    && apt-get upgrade \
    && apt-get install \
        libicu-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libxext-dev \
        libjpeg-dev \
        libpng-dev \
        libxml2-dev \
        libpq-dev \
        libzip-dev \
        tar \
        unzip \
        wget \
        curl \
        libxrender1 \
        libfontconfig1 \
        xz-utils \
    && rm -rf /var/lib/apt/lists/*

# Development tools installation
RUN apt-get update \
    && apt-get install -y git bash-completion git-flow \
    && curl -sS https://raw.githubusercontent.com/git/git/${GIT_VERSION}/contrib/completion/git-completion.bash > ~/.git-completion.bash \
    && curl -sS https://raw.githubusercontent.com/git/git/${GIT_VERSION}/contrib/completion/git-prompt.sh > ~/.git-prompt.sh \
    && curl -sS https://raw.githubusercontent.com/petervanderdoes/git-flow-completion/master/git-flow-completion.bash > ~/.git-flow-completion.bash \
    && echo "\nsource ~/.git-prompt.sh\nsource ~/.git-completion.bash\nsource ~/.git-flow-completion.bash\n" >> ~/.bashrc \
    && rm -rf /var/lib/apt/lists/*

# PHP tools installation
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && chmod +x /usr/local/bin/composer \
    && php -r "unlink('composer-setup.php');"

# Node.js and frontend tools
RUN apt-get update && apt-get install -y gnupg

# Ajouter la clé Yarn
RUN mkdir -p /etc/apt/keyrings \
 && curl -fsSL https://dl.yarnpkg.com/debian/pubkey.gpg | gpg --dearmor -o /etc/apt/keyrings/yarn.gpg

# Ajouter le dépôt Yarn
RUN echo "deb [signed-by=/etc/apt/keyrings/yarn.gpg] https://dl.yarnpkg.com/debian stable main" \
    > /etc/apt/sources.list.d/yarn.list


RUN curl -sL https://deb.nodesource.com/setup_lts.x | bash - \
    && apt-get install -y nodejs \
    && curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | gpg --dearmor -o ... \
    && echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list \
    && apt-get update && apt-get install -y yarn \
    && npm install -g gulp \
    && rm -rf /var/lib/apt/lists/*

# Apache and PHP configuration
COPY ./custom.ini "$PHP_INI_DIR/conf.d/"
COPY ./opcache.ini "$PHP_INI_DIR/conf.d/"
COPY ./000-default.conf "/etc/apache2/sites-enabled/"
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" \
    && cp /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled/ \
    && cp /etc/apache2/mods-available/headers.load /etc/apache2/mods-enabled/

# PHP extensions installation
RUN docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-install -j$(nproc) \
        intl \
        pdo_mysql \
        gd \
        zip \
        bcmath \
        soap \
        opcache \
        exif

# Set working directory
WORKDIR /var/www/html

# Configure git to not prompt for credentials
RUN git config --global --add safe.directory /var/www/html

# Shell customization
RUN echo '\n\nexport PS1="\[\e[38;5;39m\][Docker]\[\033[00m\]\u@\h:\w\[\033[0;33m\]\$(__git_ps1)\[\033[00m\] \\n\[\e[38;5;39m\]\\$\[\033[00m\] "' >> ~/.bashrc