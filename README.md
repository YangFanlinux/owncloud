# owncloud
support ceph s3 for primary storage

一.前言
在阅读此文档前，建议先阅读owncloud官方的《ownCloud_Server_Administration_Manual.pdf》4.2节，对owncloud部署方式有所了解。本期项目通过对owncloud的开发，使其支持后端S3存储，在部署架构上，S3存储端完全可以支持owncloud部署要求的“Large Enterprises and Service Providers”，从功能、性能、可靠性、可扩展性、容量等方面满足大规模云存储网盘的使用规模。
二.部署方法
本文以“Small Workgroups or Departments”为例描述单机owncloud对接S3的部署方式，其他部署方式可以参考ownclud admin手册。本文档部署环境使用Ubuntu14.04 x64。

1. 安装owncloud之前，首先需要Apache, MySQL/MariaDB, PHP的支持，以下使用MySQL作为数据库进行安装。
sudo apt-get install apache2 mysql-server libapache2-mod-php5 
sudo apt-get install php5-gd php5-json php5-mysql php5-curl 
sudo apt-get install php5-intl php5-mcrypt php5-imagick
第一行    安装运行完之后，MySQL需要配置root用户的密码。

2. 安装ownCloud，从GitLab下载owncloud源码包，解压缩到/var/www目录下。至此，owncloud安装完毕。

3. Apache服务器配置,首先切换到root用户
    su root
cd /etc/apache2/sites-available/ 
vim owncloud.conf

4. 将以下文字复制进owncloud.conf配置文件。
Alias /owncloud "/var/www/owncloud/"
<Directory "/var/www/owncloud">
    Options +FollowSymLinks
    AllowOverride All

    <IfModule mod_dav.c>
      Dav off
    </IfModule>
      Satisfy Any
    SetEnv HOME /var/www/owncloud
    SetEnv HTTP_HOME /var/www/owncloud
</Directory>

<Directory "/var/www/owncloud/data/">
  # just in case if .htaccess gets disabled
  Require all denied
</Directory>

5. 将配置文件symlink到/etc/apache2/sites-enabled下。
ln -s /etc/apache2/sites-available/owncloud.conf /etc/apache2/sites-enabled/owncloud.conf

6. 创建文件链接后，可以看到/etc/apache2/sites-enabled/文件夹下多了一个owncloud.conf文件，此文件的更改和/etc/apache2/sites-available/下owncloud.conf文件的更改同步。

7. 接下来为可选设置，但建议还是设置一下
a2enmod rewrite 
a2enmod headers 
a2enmod env 
a2enmod dir 
a2enmod mime

8. 重启Apache
service apache2 restart

9. 开启SSL
a2enmod ssl 
a2ensite default-ssl 
service apache2 reload

10. ownCloud 配置，首先，把owncloud目录的给你的HTTP user，默认为www-data
chown -R www-data:www-data /var/www/owncloud/

11. 接下来，使用浏览器访问以下地址，其中localhost使用主机内网IP代替。注意，先不要创建管理员帐号并登录。只登录一下系统，这样在/var/www/owncloud/config/目录下会生成默认配置文件。
http://localhost/owncloud

12.修改配置文件/var/www/owncloud/config/config.php，如果使用multibucket特性可以参考config.multibucket.php的配置。如果使用单bucket可以参考config-singbucket.php。以multibucket为例，在config.php中加入如下配置：
  'objectstore_multibucket'=> array (
    'class' => 'OC\\Files\\ObjectStore\\CephS3',
    'arguments' => array (
      'autocreate' => true,//自动创建bucket，默认为true
      'version' => '2006-03-01',//AWS s3版本号
      'region' => '',//连接AWS需要设置，ceph s3不需要设置
      'key' => 'E60Z7V7OW9Y1U8WFI9T3', //s3 rgw的key
      'secret' => 'yf',           // s3 rgw的secret
      'endpoint' => 'http://192.168.74.128:80/',//ceph s3的访问地址
      'PathStyle' => true,//连接ceph为true,aws为false
      'prefix' => 'neunnowncloud-',//multi-bucket需要填写生成bucket的前缀，注意，需要参考s3 bucket命名规范。
      'hashlength' => '3',//系统自动生成的bucket名字为prefix+hash，默认取userid md5值的前三位作为hash值。
    ),
     'integrity.check.disabled' => true,//由于进行了代码二次开发，关闭程序完整性校验功能
	  'enable_previews' => false,//文件预览功能有bug，暂时关闭
	  
13.设置完后访问http://localhost/owncloud，创建管理员并填写数据库信息后，即可登录，owncloud的所有部署工作完成。

三.关于owncloud版本升级
	本次开发基于owncloud最新稳定版本9.1.1，源码进行了一定的改动并没有合并入owncloud主线版本，所以后续升级不支持官方的自动升级，需要通知开发人员对应owncloud新版本的代码更新。
