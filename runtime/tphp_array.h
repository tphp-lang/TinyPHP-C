/*
 * Array：纯列表（0 基连续、整数下标、越界检查），单一泛型结构
 * （voidptr + element_size 方案，见 doc/type.md）。
 *
 * 引用语义（赋值共享指针）；v0.1 不主动回收（refcount 基础设施为后续版本就位）。
 * elem_flags 供运行时释放嵌套引用时使用：
 *   0 = 裸值  1 = 元素是 Array*  2 = 元素是对象指针
 */
#ifndef TPHP_ARRAY_H
#define TPHP_ARRAY_H

#define TPHP_ELEM_RAW 0
#define TPHP_ELEM_ARRAY 1
#define TPHP_ELEM_OBJECT 2
#define TPHP_ELEM_IFACE 3 /* 元素是 TphpIface 胖指针（16 字节），按 obj 字段释放 */

typedef struct {
    int32_t length;
    int32_t capacity;
    int32_t refcount;
    int32_t element_size;
    int32_t elem_flags;
    void *data;
} Array;

/* 前置声明：push_* 会引用文件末尾的引用计数函数 */
static void tphp_arr_ref(Array *a);
static void tphp_arr_unref(Array *a);

static Array *tphp_arr_new(int32_t element_size, int32_t capacity, int32_t elem_flags)
{
    Array *a = (Array *)malloc(sizeof(Array));
    if (!a) {
        tphp_panic("out of memory");
    }
    a->length = 0;
    a->capacity = capacity > 4 ? capacity : 4;
    a->refcount = 1;
    a->element_size = element_size;
    a->elem_flags = elem_flags;
    a->data = malloc((size_t)a->capacity * (size_t)element_size);
    if (!a->data) {
        tphp_panic("out of memory");
    }
    tphp_mem_track_array(1);
    return a;
}

static Array *tphp_arr_grow(Array *a, int32_t needed)
{
    if (needed <= a->capacity) {
        return a;
    }
    int32_t cap = a->capacity * 2;
    if (cap < needed) {
        cap = needed;
    }
    void *d = realloc(a->data, (size_t)cap * (size_t)a->element_size);
    if (!d) {
        tphp_panic("out of memory");
    }
    a->data = d;
    a->capacity = cap;
    return a;
}

/* 追加：可能扩容并返回新数组指针，调用侧必须重新赋值变量 */
static Array *tphp_arr_push(Array *a, const void *value)
{
    a = tphp_arr_grow(a, a->length + 1);
    memcpy((char *)a->data + (size_t)a->length * (size_t)a->element_size,
           value, (size_t)a->element_size);
    a->length++;
    return a;
}

static void tphp_arr_bounds(Array *a, int32_t i)
{
    if (!a) {
        tphp_panic("Accessing an index of a null array");
    }
    if (i < 0 || i >= a->length) {
        tphp_panic("array index out of bounds");
    }
}

/* ---------------------------------------------------------------- 追加 */

static Array *tphp_arr_push_int(Array *a, int32_t v) { return tphp_arr_push(a, &v); }
static Array *tphp_arr_push_double(Array *a, double v) { return tphp_arr_push(a, &v); }
static Array *tphp_arr_push_float(Array *a, float v) { return tphp_arr_push(a, &v); }
static Array *tphp_arr_push_bool(Array *a, bool v) { return tphp_arr_push(a, &v); }
static Array *tphp_arr_push_str(Array *a, String v) { return tphp_arr_push(a, &v); }
static Array *tphp_arr_push_raw(Array *a, const void *v) { return tphp_arr_push(a, v); }

static Array *tphp_arr_push_arr(Array *a, Array *v)
{
    tphp_arr_ref(v);
    return tphp_arr_push(a, &v);
}

static Array *tphp_arr_push_obj(Array *a, void *v)
{
    tphp_object_ref(v);
    return tphp_arr_push(a, &v);
}

/* ---------------------------------------------------------------- 读取 */

static int32_t tphp_arr_get_int(Array *a, int32_t i)
{
    tphp_arr_bounds(a, i);
    return ((int32_t *)a->data)[i];
}

static double tphp_arr_get_double(Array *a, int32_t i)
{
    tphp_arr_bounds(a, i);
    return ((double *)a->data)[i];
}

static float tphp_arr_get_float(Array *a, int32_t i)
{
    tphp_arr_bounds(a, i);
    return ((float *)a->data)[i];
}

static bool tphp_arr_get_bool(Array *a, int32_t i)
{
    tphp_arr_bounds(a, i);
    return ((bool *)a->data)[i];
}

static String tphp_arr_get_str(Array *a, int32_t i)
{
    tphp_arr_bounds(a, i);
    return ((String *)a->data)[i];
}

static Array *tphp_arr_get_arr(Array *a, int32_t i)
{
    tphp_arr_bounds(a, i);
    return ((Array **)a->data)[i];
}

static void *tphp_arr_get_obj(Array *a, int32_t i)
{
    tphp_arr_bounds(a, i);
    return ((void **)a->data)[i];
}

/* c.* 元素按原始字节读取 */
static void tphp_arr_get_raw(Array *a, int32_t i, void *out)
{
    tphp_arr_bounds(a, i);
    memcpy(out, (char *)a->data + (size_t)i * (size_t)a->element_size, (size_t)a->element_size);
}

/* ---------------------------------------------------------------- 写入 */

static void tphp_arr_set_int(Array *a, int32_t i, int32_t v)
{
    tphp_arr_bounds(a, i);
    ((int32_t *)a->data)[i] = v;
}

static void tphp_arr_set_double(Array *a, int32_t i, double v)
{
    tphp_arr_bounds(a, i);
    ((double *)a->data)[i] = v;
}

static void tphp_arr_set_float(Array *a, int32_t i, float v)
{
    tphp_arr_bounds(a, i);
    ((float *)a->data)[i] = v;
}

static void tphp_arr_set_bool(Array *a, int32_t i, bool v)
{
    tphp_arr_bounds(a, i);
    ((bool *)a->data)[i] = v;
}

static void tphp_arr_set_str(Array *a, int32_t i, String v)
{
    tphp_arr_bounds(a, i);
    ((String *)a->data)[i] = v;
}

static void tphp_arr_set_raw(Array *a, int32_t i, const void *v)
{
    tphp_arr_bounds(a, i);
    memcpy((char *)a->data + (size_t)i * (size_t)a->element_size, v, (size_t)a->element_size);
}

static void tphp_arr_set_obj(Array *a, int32_t i, void *v)
{
    tphp_arr_bounds(a, i);
    tphp_object_ref(v);
    void **slot = (void **)a->data + i;
    if (*slot) {
        tphp_object_unref(*slot); /* 释放被覆盖的旧元素 */
    }
    *slot = v;
}

static void tphp_arr_set_arr(Array *a, int32_t i, Array *v)
{
    tphp_arr_bounds(a, i);
    tphp_arr_ref(v);
    Array **slot = (Array **)a->data + i;
    if (*slot) {
        tphp_arr_unref(*slot); /* 释放被覆盖的旧元素 */
    }
    *slot = v;
}

/* ---------------------------------------------------------------- 其他 */

static int32_t tphp_len_arr(Array *a)
{
    if (!a) {
        tphp_panic("len() applied to a null array");
    }
    return a->length;
}

static void tphp_arr_ref(Array *a)
{
    if (a) {
        a->refcount++;
    }
}

static void tphp_arr_unref(Array *a)
{
    if (a && --a->refcount == 0) {
        if (a->elem_flags == TPHP_ELEM_ARRAY) {
            Array **items = (Array **)a->data;
            for (int32_t i = 0; i < a->length; i++) {
                tphp_arr_unref(items[i]);
            }
        } else if (a->elem_flags == TPHP_ELEM_OBJECT) {
            void **items = (void **)a->data;
            for (int32_t i = 0; i < a->length; i++) {
                tphp_object_unref(items[i]);
            }
        } else if (a->elem_flags == TPHP_ELEM_IFACE) {
            /* 元素是 TphpIface（obj+itab 两个指针）：步长 = element_size/8，
             * 只释放每个元素的 obj 字段 */
            int32_t stride = a->element_size / (int32_t)sizeof(void *);
            void **items = (void **)a->data;
            for (int32_t i = 0; i < a->length; i++) {
                tphp_object_unref(items[i * stride]);
            }
        }
        free(a->data);
        free(a);
        tphp_mem_track_array(-1);
    }
}

#endif /* TPHP_ARRAY_H */
