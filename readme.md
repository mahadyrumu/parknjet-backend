# How to Setup for development.
Install docker
git checkout this repository
Run `cp .env.example .env`
Run `./vendor/bin/sail up`
Run `./vendor/bin/sail composer dump-autoload`
Run `npm run dev` - (For Tailwind css).

## Input fields:

1. Select Parking Lot - Radio button
2. Check In Date and Time - date time field
3. Check out date and time - date time field
4. Parking type - radio button
5. Vehicle lenght - radio button 
    a. Under 17ft
    b. 17-19ft (Note: Park N Jet Lot 2 has the option of accepting over 19ft. vehicles)
    c. 19-21ft (show in lot 2)
6. Vehicle License Plate
7. Vehicle Make and Model
8. Driver information
    a. Full name
    b. Phone number
    c. Email (optional)
    d. Total number of people on return including driver - (0 to 10+)
9. Return Flight (optional)
10. coupone code
11. payment option
    a. Pay now - save 5%
    b. Pay at the Lot

## Reservation summery
## Invoice


## Change port to 9000 insted of 80
Add APP_PORT=9000 in .env file
sail build --no-cache

## Manual SQL Migration

#Database : pnjserver_r2

http://pnj.localhost:5173/v1/
http://pnj.localhost/b/v1/dashboard

## For new project setup, remove existing project Docker Image & Volumnes
## Or If the Playground databases are not connecting run this command

docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs


# PNJ API Documentation
API Documentation is available on documentation folder in this project. It's on JSON format. You can import it on Postman and run the test