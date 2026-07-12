import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('46.202.172.5', port=65002, username='u913239002', password='Furquan5@')

# Upload files
sftp = client.open_sftp()
sftp.put(r'C:\xampp\htdocs\wordpress\wp-content\themes\kadence\inc\mcprices\pattern-homepage.php', './domains/mcdomenuusa.com/deploy/current/wp-content/themes/kadence/inc/mcprices/pattern-homepage.php')
sftp.put(r'C:\xampp\htdocs\wordpress\wp-content\themes\kadence\inc\mcprices\pattern-homepage-source.php', './domains/mcdomenuusa.com/deploy/current/wp-content/themes/kadence/inc/mcprices/pattern-homepage-source.php')
sftp.close()
print('Upload successful')
