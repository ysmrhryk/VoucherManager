import qs from 'qs';

const stringify = (filter: object, page: number) => {
    const query = qs.stringify(
        { 
            filter: filter,
            page: page
        },
        {
            arrayFormat: 'brackets',
            addQueryPrefix: true,
            filter: (prefix, value) => {
                if(value === null || value === '') return;
                
                return value;
            },
        }
    );

    return query;
}

export default stringify;