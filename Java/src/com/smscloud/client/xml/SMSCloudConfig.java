package com.smscloud.client.xml;
import java.net.MalformedURLException;
import java.net.URL;
import java.util.TimeZone;

import org.apache.xmlrpc.client.XmlRpcHttpClientConfig;

public class SMSCloudConfig implements XmlRpcHttpClientConfig {

	@Override
	public String getBasicPassword() {
		return null;
	}

	@Override
	public String getBasicUserName() {
		return null;
	}

	@Override
	public int getConnectionTimeout() {
		return 0;
	}

	@Override
	public int getReplyTimeout() {
		return 0;
	}

	@Override
	public boolean isEnabledForExceptions() {
		return false;
	}

	@Override
	public boolean isGzipCompressing() {
		return false;
	}

	@Override
	public boolean isGzipRequesting() {
		return false;
	}

	@Override
	public String getEncoding() {
		return null;
	}

	@Override
	public TimeZone getTimeZone() {
		return null;
	}

	@Override
	public boolean isEnabledForExtensions() {
		return false;
	}

	@Override
	public String getBasicEncoding() {
		return null;
	}

	@Override
	public boolean isContentLengthOptional() {
		return false;
	}

	@Override
	public URL getServerURL() {
		try {
			return new URL("http://api.smscloud.com/xmlrpc");
		} catch (MalformedURLException e) {
			return null;
		}
	}

	@Override
	public String getUserAgent() {
		return null;
	}

}
