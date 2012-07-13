package com.smscloud.client.xml;
import java.net.MalformedURLException;
import java.net.URL;

import org.apache.xmlrpc.client.XmlRpcClient;
import org.apache.xmlrpc.client.XmlRpcClientConfigImpl;

import com.smscloud.client.ClientException;
import com.smscloud.client.SMSCloudInterface;


public class SMSCloud implements SMSCloudInterface {
	protected XmlRpcClient client;
	protected XmlRpcClientConfigImpl clientConfig;

	public SMSCloud() throws MalformedURLException {
		clientConfig = new XmlRpcClientConfigImpl();
		clientConfig.setServerURL(new URL("http://api.smscloud.com/xmlrpc?key="));

		client = new XmlRpcClient();
		client.setConfig(clientConfig);
	}

	public Object sendSms(String from, String to, String message) throws ClientException {
		Object[] params = new Object[]{from, to, message, new Integer(0)};
		Object response = null;

		try {
			response = client.execute("sms.send", params);
		} catch (Exception e) {
			throw new ClientException(e);
		}

		return response;
	}

	@Override
	public Object carrierLookup(String number) throws ClientException {
		Object[] params = new Object[]{number};
		Object response = null;

		try {
			response = client.execute("nvs.carrierLookup", params);
		} catch (Exception e) {
			throw new ClientException(e);
		}

		return response;
	}
}
