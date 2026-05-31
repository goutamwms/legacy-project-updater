import { type ReactNode } from 'react'
import * as DialogPrimitive from '@radix-ui/react-dialog'
import { X } from 'lucide-react'
import { cn } from '../../lib/utils'

function Dialog({ children, open, onOpenChange }: {
  children: ReactNode
  open?: boolean
  onOpenChange?: (open: boolean) => void
}) {
  return (
    <DialogPrimitive.Root open={open} onOpenChange={onOpenChange}>
      {children}
    </DialogPrimitive.Root>
  )
}

function DialogTrigger({ children, asChild }: { children: ReactNode; asChild?: boolean }) {
  return (
    <DialogPrimitive.Trigger asChild={asChild}>{children}</DialogPrimitive.Trigger>
  )
}

function DialogContent({ children, className }: { children: ReactNode; className?: string }) {
  return (
    <DialogPrimitive.Portal>
      <DialogPrimitive.Overlay className="fixed inset-0 z-40 bg-black/60 data-[state=open]:animate-in data-[state=closed]:animate-out" />
      <DialogPrimitive.Content
        className={cn(
          'fixed left-1/2 top-1/2 z-50 w-[90vw] max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-2xl bg-white p-8 shadow-2xl',
          className
        )}
      >
        {children}
        <DialogPrimitive.Close className="absolute right-5 top-5 text-gray-400 hover:text-gray-600">
          <X className="size-5" />
        </DialogPrimitive.Close>
      </DialogPrimitive.Content>
    </DialogPrimitive.Portal>
  )
}

function DialogTitle({ children }: { children: ReactNode }) {
  return <DialogPrimitive.Title className="text-2xl font-bold text-gray-800 mb-1">{children}</DialogPrimitive.Title>
}

function DialogDescription({ children }: { children: ReactNode }) {
  return <DialogPrimitive.Description className="text-sm text-gray-500 mb-6">{children}</DialogPrimitive.Description>
}

export { Dialog, DialogTrigger, DialogContent, DialogTitle, DialogDescription }
