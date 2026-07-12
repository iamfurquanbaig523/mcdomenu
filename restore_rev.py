import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('46.202.172.5', port=65002, username='u913239002', password='Furquan5@')

cmd = """cd domains/mcdomenuusa.com/public_html && php -r '
require \"wp-load.php\";
require_once ABSPATH . \"wp-admin/includes/post.php\";
wp_restore_post_revision(22592);
echo \"Restored revision 22592\\n\";
' && wp cache flush"""

stdin, stdout, stderr = client.exec_command(cmd)
print('OUT:', stdout.read().decode())
print('ERR:', stderr.read().decode())
