/*
 * 对象：编译期单态化的 C struct。头三字段为引用计数、vtable、析构指针。
 * 引用计数归零时先调用 dtor 释放堆字段，再释放对象本身。
 */
#ifndef TPHP_OBJECT_H
#define TPHP_OBJECT_H

#define TPHP_OBJECT_HEAD int32_t refcount; void *vt; void (*dtor)(void *);

/* ------------------------------------------------- 内存统计（--mem-stats） */

static int64_t tphp_mem_arr_allocs = 0;
static int64_t tphp_mem_arr_frees = 0;
static int64_t tphp_mem_obj_allocs = 0;
static int64_t tphp_mem_obj_frees = 0;
static int64_t tphp_mem_cmem_allocs = 0;
static int64_t tphp_mem_cmem_frees = 0;
static bool tphp_mem_stats_on = false;

static void tphp_mem_track_array(int delta) { tphp_mem_arr_allocs += delta > 0; tphp_mem_arr_frees += delta < 0; }
static void tphp_mem_track_object(int delta) { tphp_mem_obj_allocs += delta > 0; tphp_mem_obj_frees += delta < 0; }
static void tphp_mem_track_cmem(int delta) { tphp_mem_cmem_allocs += delta > 0; tphp_mem_cmem_frees += delta < 0; }

typedef struct {
    int32_t refcount;
    void *vt;
    void (*dtor)(void *);
} TphpObjHead;

static void *tphp_object_alloc(size_t size, void *vt, void (*dtor)(void *))
{
    TphpObjHead *o = (TphpObjHead *)calloc(1, size);
    if (!o) {
        tphp_panic("out of memory");
    }
    o->refcount = 1;
    o->vt = vt;
    o->dtor = dtor;
    tphp_mem_track_object(1);
    return o;
}

static void tphp_object_ref(void *obj)
{
    if (obj) {
        ((TphpObjHead *)obj)->refcount++;
    }
}

static void tphp_object_unref(void *obj)
{
    if (obj && --((TphpObjHead *)obj)->refcount == 0) {
        void (*dtor)(void *) = ((TphpObjHead *)obj)->dtor;
        if (dtor) {
            dtor(obj); /* 先释放字段，再释放对象本身 */
        }
        free(obj);
        tphp_mem_track_object(-1);
    }
}

/* 经 vtable 分发方法调用：TPHP_VT(obj, tphp_vt_Animal)->speak(obj) */
#define TPHP_VT(obj, vttype) ((const vttype *)(obj)->vt)

/*
 * 接口胖指针（Go itab 风格）：所有接口共用此 struct。
 * obj 为对象指针；itab 指向 (类, 接口) 对应的静态方法表，
 * 调用侧按静态接口类型转型为 tphp_itab_<I>*。
 */
typedef struct {
    void *obj;
    const void *itab;
} TphpIface;

static void tphp_mem_report(void)
{
    if (!tphp_mem_stats_on) {
        return;
    }
    int64_t leaks = (tphp_mem_arr_allocs - tphp_mem_arr_frees)
        + (tphp_mem_obj_allocs - tphp_mem_obj_frees)
        + (tphp_mem_cmem_allocs - tphp_mem_cmem_frees);
    printf("mem: arrays %lld/%lld objects %lld/%lld cmem %lld/%lld leaks=%lld\n",
           (long long)tphp_mem_arr_allocs, (long long)tphp_mem_arr_frees,
           (long long)tphp_mem_obj_allocs, (long long)tphp_mem_obj_frees,
           (long long)tphp_mem_cmem_allocs, (long long)tphp_mem_cmem_frees,
           (long long)leaks);
    fflush(stdout);
}

#endif /* TPHP_OBJECT_H */
