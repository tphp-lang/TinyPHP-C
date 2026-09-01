/*
 * Callable：闭包 / C 函数指针（doc/type.md）。
 * v0.1 仅支持静态函数引用与 null；env 字段为闭包捕获环境预留。
 */
#ifndef TPHP_CALLABLE_H
#define TPHP_CALLABLE_H

typedef struct {
    void *fn;
    void *env;
} Callable;

#endif /* TPHP_CALLABLE_H */
