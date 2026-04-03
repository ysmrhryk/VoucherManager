export const formatDate = (arg?: string | null): string | null => {
    if(arg){
        return new Date(arg).toLocaleString('ja-JP', {
            year: "numeric", 
            month: "2-digit", 
            day: "2-digit", 
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        }).replaceAll('/', '-');
    }else{
        return null;
    }
}

export const formatNumber = (arg: string | number): string => {
    return Number(arg).toLocaleString('ja-JP');
}