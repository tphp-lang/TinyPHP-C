#include "phpc_util.h"
#include <math.h>
#include <stdlib.h>
#include <string.h>

Point2D *util_point_new(int32_t x, int32_t y)
{
    Point2D *p = (Point2D *)malloc(sizeof(Point2D));
    if (!p) {
        return 0;
    }
    p->x = x;
    p->y = y;
    return p;
}

void util_point_scale(Point2D *p, int32_t f)
{
    p->x *= f;
    p->y *= f;
}

int32_t util_point_dot(Point2D *p)
{
    return p->x * p->y;
}

char *util_join(const char *a, const char *b)
{
    size_t la = strlen(a);
    size_t lb = strlen(b);
    char *s = (char *)malloc(la + lb + 1);
    if (!s) {
        return 0;
    }
    memcpy(s, a, la);
    memcpy(s + la, b, lb + 1);
    return s;
}

const char *util_version(void)
{
    return UTIL_VERSION;
}

int64_t util_big_i64(void)
{
    return 9000000000LL;
}

int64_t util_sum_i64(int64_t a, int64_t b)
{
    return a + b;
}

uint32_t util_overflow_u32(void)
{
    return 4000000000u + 1000000000u; /* 无符号回绕 = 705032704 */
}

double util_hypot2(double x, double y)
{
    return sqrt(x * x + y * y);
}

int32_t util_count_char(const char *s, char c)
{
    int32_t n = 0;
    while (*s) {
        if (*s == c) {
            n++;
        }
        s++;
    }
    return n;
}
