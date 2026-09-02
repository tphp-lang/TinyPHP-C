#ifndef CFN_HELPER_H
#define CFN_HELPER_H

#include <stdint.h>

/* c_fn 回调测试辅助库：C 侧以"尾参 void* userdata"约定调用回调 */

typedef int32_t (*cfn_cb)(int32_t, void *);

/* 以 (x, ud) 调用 cb 并返回其结果 +10 */
int32_t cfn_apply(int32_t x, cfn_cb cb, void *ud);

#endif /* CFN_HELPER_H */
