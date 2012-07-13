package com.smscloud.client;

public interface SMSCloudInterface {
	public Object sendSms(String from, String to, String message) throws ClientException;
	public Object carrierLookup(String number) throws ClientException;
}
