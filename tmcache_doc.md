### tmcache - TieMa(Tiny&Mini) Memory Cache Server (Daemon) ###

tmcache is a very small memory cache server, It run from daemon. It is similar to memcachd, and fully compatible with the memcached communication protocol can be easily carried from the memcached to tmcache transplant. tmcache is based on the thread to run, the faster the speed.


### tmcache including: ###

  * Based memory data storage
  * Compatible memcached communication protocol
  * Few operation interface, The use of simple
  * Support custom port,max\_clients,memory use control
  * ...


###  ###
###  ###

### tmhttpd command help ###

```
#=======================================
# TieMa(Tiny&Mini) Memory Cache Server
# Version 1.0.0_alpha
# 
# heiyeluren <blog.csdn.net/heiyeshuwu>
#=======================================

usage: ./tmcache [OPTION] ... 

Options: 
  -p <num>      port number to listen on,default 11211
  -d            run as a daemon, default No
  -m <num>      max memory to use for items in megabytes, default is 16M
  -c <num>      max simultaneous connections, default is 1024
  -v            print version information
  -h            print this help and exit

Example: 
  ./tmcache -p 11211 -m 16 -c 1024 -d



```