#include "phpc_demo.h"

int32_t demo_area(DemoSize s)
{
    return s.w * s.h;
}

int32_t demo_add(int32_t a, int32_t b)
{
    return a + b;
}

const char *demo_greet(void)
{
    return "hello from C";
}
