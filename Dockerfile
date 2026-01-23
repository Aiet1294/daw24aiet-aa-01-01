#Docker Hub-eko PHP + Apache-ko irudi ofiziala

FROM php:8.2-apache

# Web-aplikazioaren fitxategiak kontenedore barruan kopiatu 

COPY . /var/www/html

#Apache web zerbitzariak erabiltzen duen portuaren informazioa erakutsi

EXPOSE 80
