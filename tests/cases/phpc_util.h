#ifndef PHPC_UTIL_H
#define PHPC_UTIL_H

#include <stdint.h>

/* phpc 扩展测试库：结构体指针 / 大数 / 无符号回绕 / 字符串拼接 / 静态数据 */

#define UTIL_VERSION "1.0"

typedef struct {
    int32_t x;
    int32_t y;
} Point2D;

Point2D *util_point_new(int32_t x, int32_t y);
void util_point_scale(Point2D *p, int32_t f);
int32_t util_point_dot(Point2D *p);
char *util_join(const char *a, const char *b);
const char *util_version(void);
int64_t util_big_i64(void);
int64_t util_sum_i64(int64_t a, int64_t b);
uint32_t util_overflow_u32(void);
double util_hypot2(double x, double y);
int32_t util_count_char(const char *s, char c);

#endif /* PHPC_UTIL_H */
