CREATE DATABASE azebo2;
CREATE USER '<yourUsername>' IDENTIFIED BY '<yourPassword>';
GRANT USAGE ON *.*  TO  '<yourUsername>'@localhost IDENTIFIED BY '<yourPassword>';
GRANT ALL PRIVILEGES ON azebo2.* TO '<yourUsername>'@localhost IDENTIFIED BY '<yourPassword>';
