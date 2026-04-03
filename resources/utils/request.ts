import api from "@/ts/api"
import type { NameSuffix, Client } from "@/interfaces/Index";
import stringify from "./query";

const transaction_types = async () => {
    try{
        const res = await api.get('/transaction-types');
        return res.data;
    }catch(err){
        //
    }
}

const tax_rates = async () => {
    try{
        const res = await api.get('/tax-rates');
        return res.data;
    }catch(err){
        //
    }
}

const name_suffixes = async () => {
    let name_suffixes: NameSuffix[] = [];

    try{
        const res = await api.get('/name-suffixes');
        name_suffixes = res.data;
    }catch(err){
        //
    }finally{
        return name_suffixes;
    }
}

const billing_cycle_types = async () => {
    try{
        const res = await api.get('/billing-cycle-types');
        return res.data;
    }catch(err){
        //
    }
}

const payment_methods = async () => {
    try{
        const res = await api.get('/payment-methods');
        return res.data;
    }catch(err){
        //
    }
}

const service_voucher_row_types = async () => {
    try{
        const res = await api.get('/service-voucher-row-type');
        return res.data;
    }catch(err){
        //
    }
}

const estimate_voucher_row_types = async () => {
    try{
        const res = await api.get('/estimate-voucher-row-type');
        return res.data;
    }catch(err){
        //
    }
}

const clients = {
    show: async ({ id, code }: { id?: number | null, code?: string | null }) => {
        try{
            // stringifyにいつか切り替えたい

            if(id){
                let response = await api.get(`/clients/${id}`);

                return response?.data;
            }else if(code){
                let response = await api.get(`/clients/?filter[code]=${code}`);

                if(response?.data?.data?.length === 1) return response.data.data[0];
                else return null;
            }else{
                return null;
            }
        }catch(err){
            //
        }
    },
    index: async ({ keywords, page }: { keywords: string | null, page: number }) => {
        try{
            // wakaranai...

            let response = await api.get(`/clients/?page=${page}&filter[keywords]=${keywords}`);

            return response.data;
        }catch(err){
            //
        }
    }
}

const users = {
    show: async ({ id, code }: { id?: number | null, code?: string | null }) => {
        try{
            // stringifyにいつか切り替えたい

            if(id){
                let response = await api.get(`/users/${id}`);

                return response?.data;
            }else if(code){
                let response = await api.get(`/users/?filter[code]=${code}`);

                if(response?.data?.data?.length === 1) return response.data.data[0];
                else return null;
            }else{
                return null;
            }
        }catch(err){
            //
        }
    },
    index: async ({ keywords, page }: { keywords: string | null, page: number }) => {
        try{
            // wakaranai...

            let response = await api.get(`/users/?page=${page}&filter[keywords]=${keywords}`);

            return response.data;
        }catch(err){
            //
        }
    }
}

export default {
    transaction_types,
    name_suffixes,
    billing_cycle_types,
    payment_methods,
    tax_rates,
    service_voucher_row_types,
    estimate_voucher_row_types,
    clients,
    users
}