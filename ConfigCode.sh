sudo apt update && sudo apt upgrade -y



cd Documents/
git clone https://github.com/MonostoriMark/13_S1_2_vizsgaremek.git



sudo apt install -y python3-pip
python3 -m pip install --upgrade pip
python3 -m pip install pyzbar picamera2 pymysql requests fastapi uvicorn paho-mqtt myqtt.client



# Add cloudflare gpg key
sudo mkdir -p --mode=0755 /usr/share/keyrings
curl -fsSL https://pkg.cloudflare.com/cloudflare-public-v2.gpg | sudo tee /usr/share/keyrings/cloudflare-public-v2.gpg >/dev/null

# Add this repo to your apt repositories
echo 'deb [signed-by=/usr/share/keyrings/cloudflare-public-v2.gpg] https://pkg.cloudflare.com/cloudflared any main' | sudo tee /etc/apt/sources.list.d/cloudflared.list

# install cloudflared
sudo apt-get update && sudo apt-get install cloudflared

sudo cloudflared service install eyJhIjoiMmM0OGQwYjYwY2M4MWI0YjllYjA2YzdlYThlNzM2M2MiLCJ0IjoiNmViMGU0ZDQtYmVmYi00MmI4LTkyZDctMmEyYmE2ODQ4NzViIiwicyI6Ik16VTJaRGM0WlRFdE5HWmhOeTAwTm1FNUxXSm1ObVF0T1Rrd01HTmpNamt6TlRabCJ9

cloudflared tunnel run --token eyJhIjoiMmM0OGQwYjYwY2M4MWI0YjllYjA2YzdlYThlNzM2M2MiLCJ0IjoiNmViMGU0ZDQtYmVmYi00MmI4LTkyZDctMmEyYmE2ODQ4NzViIiwicyI6Ik16VTJaRGM0WlRFdE5HWmhOeTAwTm1FNUxXSm1ObVF0T1Rrd01HTmpNamt6TlRabCJ9

sudo apt install -y mosquitto
sudo systemctl start mosquitto
sudo systemctl enable mosquitto


sudo apt install -y mariadb-server mariadb-client
sudo systemctl start mariadb
sudo systemctl enable mariadb

sudo apt install -y phpmyadmin

sudo apt -y install php php-cgi php-mysqli php-pear php-mbstring libapache2-mod-php php-common php-phpseclib php-mysql

sudo mysql_secure_installation

create database hotelflowLocal;






