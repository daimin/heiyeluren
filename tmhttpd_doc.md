### tmhttpd - The tiny and mini Http Server ###

tmhttpd is a very small HTTP server. It run from daemon.
It implements all the basic features of an HTTP

### server, including: ###

  * Support GET/HEAD method
  * The common MIME types.
  * Support Self custom default index page
  * Directory listings.
  * Support access log
  * Support Self custom port and max clients
  * ...


###  ###
###  ###

### tmhttpd command help ###

```
=======================================
 TieMa(Tiny&Mini) Http Server
 Version 1.0.0 alpha

 heiyeluren <blog.csdn.net/heiyeshuwu>

=======================================

Usage: ./tmhttpd [OPTION] ...

Options:
  -D, --is-deubg        Is open debug mode, default No
  -d, --is-daemon       Is daemon running, default No
  -p, --port=PORT       Server listen port, default 80
  -m, --max-client=SIZE Max connection requests, default 100
  -L, --is-log          Is write access log, default No
  -l, --log-path=PATH   Access log path, default /tmp/tmhttpd.log
  -b, --is-browse       Is allow browse file/dir list, default No
  -r, --doc-root=PATH   Web document root directory, default programe current directory ./
  -i, --dir-index=FILE  Directory default index file name, default index.html
  -h, --help            Print help information

Example:
  ./tmhttpd -d -p 80 -m 128 -L -l /tmp/access.log -b -r /var/www -i index.html
  ./tmhttpd -d -p80 -m128 -L -l/tmp/access.log -b -r/var/www -iindex.html
  ./tmhttpd --is-daemon --port=80 --max-client=128 --is-log --log-path=/tmp/access.log --is-browse --doc-root=/var/www --dir-index=index.html


```