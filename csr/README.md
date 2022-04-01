# Replace/Renew TLS/SSL certificate

## Apache config
SSLCertificateFile "/path/to/ssl/server.cer"
SSLCertificateKeyFile "/path/to/ssl/server.key"

## Generate CSR

```sh
# Create a certificate signing request (configured in san.cnf)
openssl req -out server.csr -newkey rsa:2048 -nodes -keyout server.key -config san.cnf
```

The signed CSR become server.cer

## Replace the signed certificate and new private key

```sh
scp server.cer server:server.cer
scp server.key server:server.key

ssh server
cd /path/to/ssl

# Back up old
sudo mv server.cer server.cer.old
sudo mv server.key server.key.old

# Replace
sudo cp ~/server.crt .
sudo cp csr/server.key .

sudo service apachectl restart 
```