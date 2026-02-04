<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import type { ButtonVariants } from "."
import { Primitive } from "reka-ui"
import { cn } from "@/lib/utils"
import { buttonVariants } from "."

interface Props {
  variant?: ButtonVariants["variant"]
  size?: ButtonVariants["size"]
  class?: HTMLAttributes["class"]
  as?: any
  asChild?: boolean
  type?: "button" | "submit" | "reset"
  disabled?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  as: "button",
  type: "button",
})
</script>

<template>
  <Primitive
    :as="as"
    :as-child="asChild"
    :type="type"
    :disabled="disabled"
    :class="cn(buttonVariants({ variant, size }), props.class)"
  >
    <slot />
  </Primitive>
</template>
