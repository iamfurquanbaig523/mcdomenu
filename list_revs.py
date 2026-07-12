import paramiko

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect('46.202.172.5', port=65002, username='u913239002', password='Furquan5@')

cmd = """cd domains/mcdomenuusa.com/public_html && php -r '
require \"wp-load.php\";
$front_page_id = (int) get_option(\"page_on_front\");
$revisions = wp_get_post_revisions($front_page_id);
foreach($revisions as $rev) {
    echo \"Revision ID: \" . $rev->ID . \" | Date: \" . $rev->post_modified . \"\\n\";
}
'"""

stdin, stdout, stderr = client.exec_command(cmd)
print('OUT:', stdout.read().decode())
print('ERR:', stderr.read().decode())
