import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('46.202.172.5', port=65002, username='u913239002', password='Furquan5@')

cmd = """cd domains/mcdomenuusa.com/public_html && php -r '
require \"wp-load.php\";
$front_page_id = (int) get_option(\"page_on_front\");
$homepage_pattern = require get_theme_file_path(\"/inc/mcprices/pattern-homepage.php\");
wp_update_post(array(\"ID\" => $front_page_id, \"post_content\" => $homepage_pattern));
echo \"Updated page \" . $front_page_id . \"\\n\";
'"""

stdin, stdout, stderr = client.exec_command(cmd)
print('OUT:', stdout.read().decode())
print('ERR:', stderr.read().decode())
