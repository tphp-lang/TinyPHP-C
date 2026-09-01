#ifndef PHPH_DEMO_H
#define PHPH_DEMO_H

#include <stdint.h>

/* 测试用微型 C 库：供 12_phpuc.php 验证 phpc 互操作 */

#define DEMO_MAGIC 42

typedef struct {
    int32_t w;
    int32_t h;
    int32_t area;
} DemoSize;

int32_t demo_area(DemoSize s);
int32_t demo_add(int32_t a, int32_t b);
const char *demo_greet(void);

#endif /* PHPH_DEMO_H */
