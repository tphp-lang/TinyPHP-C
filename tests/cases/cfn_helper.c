#include "cfn_helper.h"

int32_t cfn_apply(int32_t x, cfn_cb cb, void *ud)
{
    return cb(x, ud) + 10;
}
