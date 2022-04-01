export function filterNulls(array: any[]) {
    return array.filter(val => val !== null)
}

export function rejectNulls(obj: Record<any,any>) {
    const copy: Record<any,any> = {}
    Object.entries(obj).forEach(([key, value]) => {
        if(Array.isArray(value)) {
            copy[key] = filterNulls(value)
        } else if(typeof value === 'object' && value !== null) {
            copy[key] = rejectNulls(value)
        } else if(value !== null) {
            copy[key] = value
        }
    })
    return copy
}
