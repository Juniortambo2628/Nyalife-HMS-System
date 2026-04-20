import { forwardRef, useEffect, useImperativeHandle, useRef } from 'react';

export default forwardRef(function TextInput(
    { type = 'text', className = '', isFocused = false, ...props },
    ref,
) {
    const localRef = useRef(null);

    useImperativeHandle(ref, () => ({
        focus: () => localRef.current?.focus(),
    }));

    useEffect(() => {
        if (isFocused) {
            localRef.current?.focus();
        }
    }, [isFocused]);

    return (
        <input
            {...props}
            type={type}
            className={
                'rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-white shadow-sm focus:border-pink-500 focus:ring-4 focus:ring-pink-500/20 text-black dark:text-black placeholder:text-gray-400 transition-all duration-200 ' +
                className
            }
            ref={localRef}
        />
    );
});
