/*
 * Callable：闭包 / C 函数指针（doc/closure.md）。
 *
 * 闭包值 = {fn, env}：fn 指向生成的 thunk（末参数为环境指针），
 * env 为捕获环境 / 引用盒子（tphp_env_alloc 登记，进程退出统一回收，
 * 析构先于释放执行以递减捕获的堆值引用）。
 */
#ifndef TPHP_CALLABLE_H
#define TPHP_CALLABLE_H

typedef struct {
    void *fn;
    void *env;
} Callable;

/* ------------------------------------------------ 闭包环境 / 引用盒子登记表 */

typedef struct TphpEnvBlock {
    struct TphpEnvBlock *next;
    void (*dtor)(void *env); /* 可为 NULL */
} TphpEnvBlock;

static TphpEnvBlock *tphp_env_blocks = NULL;

/** 分配闭包环境 / 引用盒子（零初始化），登记后于退出统一回收。 */
static void *tphp_env_alloc(size_t size, void (*dtor)(void *))
{
    TphpEnvBlock *b = (TphpEnvBlock *)malloc(sizeof(TphpEnvBlock) + size);
    if (b == NULL) {
        tphp_panic("out of memory");
    }
    memset(b, 0, sizeof(TphpEnvBlock) + size);
    b->next = tphp_env_blocks;
    b->dtor = dtor;
    tphp_env_blocks = b;
    return (char *)b + sizeof(TphpEnvBlock);
}

/** atexit 回调：先执行各环境析构（递减捕获的堆值引用），再释放。 */
static void tphp_env_free_all(void)
{
    TphpEnvBlock *b = tphp_env_blocks;
    while (b != NULL) {
        TphpEnvBlock *next = b->next;
        if (b->dtor != NULL) {
            b->dtor((char *)b + sizeof(TphpEnvBlock));
        }
        free(b);
        b = next;
    }
    tphp_env_blocks = NULL;
}

#endif /* TPHP_CALLABLE_H */
