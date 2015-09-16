# Key-Value小数据库tmdb原理和实现 #

原文：<a href='http://blog.csdn.net/heiyeshuwu/archive/2010/07/12/5728641.aspx'><a href='http://blog.csdn.net/heiyeshuwu/archive/2010/07/12/5728641.aspx'>http://blog.csdn.net/heiyeshuwu/archive/2010/07/12/5728641.aspx</a></a>

```

【原创】Key-Value小数据库tmdb发布：原理和实现


Key-Value 数据库是很早起比较典型的老式数据库，从Unix早期的dbm，后来的GNU版本的gdbm，还有ndbm，
sdbm, cdb 以及功能强大的Berkeley DB (BDB)、还有这两年风头很劲的qdbm，都是典型代表。实际上来说，
Key-Value 数据库不是严格意义上的数据库，只是一个简单快速的数据存储功能。

tmdb 也是差不多这么一个性质Key-Value小数据存储(DBM)，设定存储数据目标量级是10W级，性能嘛也不是很好，算是一个小实验型产品，说说它的基本特点：
* 存储数据量级为10W，超过后性能下降的厉害
* 因为存储特点决定，更适合存储只读数据，当然，它也是可以删除和修改数据的，只是比较浪费空间
* Key长度不能超过64个字节，数据长度不能超过65536个字节，适合存储一些小数据
* 使用的不是行级锁(Row-Level-Lock)，而且是全局锁，所以并发读写情况下，性能不是很好
* 索引文件和数据文件分离，备份情况下要全部备份
* 接口API基本是按照传统的dbm的API来设定，整个库文件较小，可直接静态编译进程序

简单说说大致的设计思路，设计方案基本是简单容易实现的方式来操作（主要是自己懒），大致说说实现，实现的比
较糟糕，请不吝指教。


【 存储结构 】

索引使用的是静态索引，Hash表的长度不能动态扩容，缺省是 65535 Hash Bucket，如果冲突的情况使用开拉链
法，那么如果冲突厉害，或者数据量大，自然大大增加了查找一条记录的时间，所以小数据量并且Key分布均匀下性能比较好（所有hash都是这样好不好 ^_^）。

上面特点说了，索引和数据文件是分离的，主要是为了动态扩容的时候不用做太多数据迁移和位置计算。

数据存储是单个文件，头部预留了256个字节的剩余，后面的都是用来存数据，所有数据都是 append 的方式，一个
数据被删除，只在索引修改标志位，不会做实际数据迁移，也不会做空闲数据空间链表记录（偷懒啊），所以结构比较简单。

看一下索引文件的存储结构：

Index File Struct:
+-----------+----------------------+--------------+----------------+
| Header    |    Key ptr buckets   | Key Record 1 |Key Record 2 .. |
+-----------+----------------------+--------------+----------------+
  256Bytes		262144Bytes(256KB)     76Bytes          76Bytes

预留了 256字节的头部空间，用来后续扩展，然后是 256K 的用来存储hash桶到一条Key的指针位置(Key 
Record)，设定的是  65536 * 4 = 256K，所以整个索引文件不能超过2G数据文件，否则单个4字节的指针空间存储不下 (^_^)。
Key Record 是存储一个Key信息的记录，一个Key信息的结构：

Index key record
+-------+--------------+----------+----------+
| Flag  |    Key       | Data ptr | Next ptr |
+-------+--------------+----------+----------+
  4Bytes	 64Bytes      4Bytes     4Bytes
  
Flag 4个字节是标志是否删除，或者别的。Key 是定长的 64 字节，Data Ptr 是数据指针，指到数据文件中具体
Value的记录位置，也是4个字节，所以决定了数据文件大小也不能超过2G (^_^)，Next ptr 是存储同样一个Hash
值下一条记录的Key记录指针 (多么简单的设计啊，就是一个内存Hash Table)。

无论Key多长，为了保证性能，都是定长方式存储，所以这个如果Key多的情况，浪费比较严重，而且实际使用中，如
果数据值比较短，一般索引文件会比数据文件更大。(-_-!)


再看看数据文件的存储结构：

Data File structure:
+----------------+----------------+-----------------+
| Header         | Data Record 1  |Data Record 2..  |
+----------------+----------------+-----------------+
  256bytes		   dynamics length  dynamics length

256个字节的预留头，然后是每个不定长的数据记录，逐个往后排列。再看看单个数据记录的结构：

Data record
+--------+-------+------------------+----------+
| Flag   | len   |   Data           | Next ptr |
+--------+-------+------------------+----------+
  4Bytes  4Bytes   dynamics length    4Bytes

4个字节的标志位(预留),4个字节存储实际数据长度，然后是下一条记录的数据指针。

整个存储结构还是比较简单明了的，因为使用了索引文件和数据文件的分开，所以很多方式实现就简单了，不过就是打开文件会多打开一个文件描述符。(^_^)


【 Hash算法 】

采用的是 BKDR Hash 算法，主要是性能比较好，其实 SDBM hash 和 Times33 都不错，不过我看这个性能更好，就选择了它。

/**
 * Hash core function
 * @desc BKDR Hash 
 */
TDBHASH _db_hash(TDB *db, const char *str) {
	TDBHASH seed = 131; //31 131 1313 13131 131313 etc..
	TDBHASH hash = 0; 
	while (*str) {
		hash = hash * seed + (*str++);
	} 
	return (hash & 0x7FFFFFFF) % db->nhash;
}


相关Hash算法比较：http://blog.csai.cn/user3/50125/archives/2009/35638.html



【tmdb 性能测试】

基本下面的测试都是在Linux下面实现，大抵CPU都是双核或者多核，Kernel 都是 2.6，文件系统基本都是Ext3，
但是在实际测试发现好的文件系统和好的系统配置，性能差异还是比较明显的。


* CentOS 5.4 测试


* Suse 11 测试


* Fedora 7 测试


* Cygwin 测试


插入数据 - 平均记录(忽略Cygwin测试): 
10W: 3.69秒
50W: 22.44秒
100W: 49.14秒


读取数据 - 平均记录(忽略Cygwin测试)：
10W: 2秒
50W: 13.26秒
100W: 32.82秒


【 tmdb 使用 】

tmdb-0.0.1下载： http://heiyeluren.googlecode.com/files/tmdb-0.0.1.zip

使用比较简单，可以编译成so共享库或者a静态库，然后包含头文件后直接使用。目前内置的Makefile会编译出输出
到 output目录，生成 libtmdb.so 和 libtmdb.a 两个库和 tmdb.h 头文件，一般我个人建议是直接使用静态
库，无依赖，还保证版本正常。目前下载的包里有 tmdb_test.c 文件，是测试性能的代码，可以参考使用，缺省
 make 会编译此文件到 output 目录，便于直接测试执行。

内置API：
TDB      *tdb_open(const char * path, char *mode);
void      tdb_close(TDB *db);
char     *tdb_fetch(TDB *db, const char *key);
STATUS    tdb_store(TDB *db, const char *key, const char *value, int mode);
STATUS    tdb_delete(TDB *db, const char *key);
void      tdb_rewind(TDB *db);
char     *tdb_nextrec(TDB *db, char *ret_key);

有点特别的是打开数据库的时候，tdb_open 的 mode 参数只能是三个值：r/c/w：
r: read only, 
c: create/truncate db, 
w: read/write


使用代码示例：(db_test.c)

#include <stdio.h>
#include <string.h>
#include <time.h>
#include "tmdb.h"

int main() {
	char *df = "db";
	TDB *db = tdb_open(df, "c");
	if ( !db ){
		printf("tdb_open() %s fail.\n", df);
		return -1;
	}
	printf("tdb_open() %s success.\n", df);

	int s;
	char *ret;
	char *key = "test_key";
	char *val = "test_value";
	s = tdb_store(db, key, val, TDB_INSERT);
	if (TDB_SUCCESS == s){
		printf("tdb_store() %s success.\n", key);
		ret = tdb_fetch(db, key);
		if (NULL != ret){
			printf("tdb_fetch() %s success, value: %s.\n", key, ret);
		}
	}
	tdb_close(db);
	printf("Close db done\n");
	
	return 0;
}


编译的时候记得加上库路径：(L 是库路径，l是库名, I是头文件路径)
$ gcc  -o db_test db_test.c -L. -ltmdb -I.
$ ./db_test
tdb_open() db success.
tdb_store() test_key success.
tdb_fetch() test_key success, value: test_value.
Close db done


【 结束 】

基本上 tmdb 只是一个比较简单容易理解的小dbm，性能不咋地，只是一个实验品，欢迎大家提出更多想法和指正不足。

更多开源代码： http://heiyeluren.googlecode.com
tmdb 下载：http://heiyeluren.googlecode.com/files/tmdb-0.0.1.zip


相关参考：
APUE: http://www.apue.com/
Haah 算法比较: http://blog.csai.cn/user3/50125/archives/2009/35638.html
bdb/gdbm/tc/sqlite3 性能测试: http://dieken-qfz.spaces.live.com/Blog/cns!586D665C0DEB512D!548.entry


```