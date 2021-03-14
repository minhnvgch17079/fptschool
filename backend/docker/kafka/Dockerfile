FROM bitnami/kafka:2.6.0

USER root
RUN apt-get update
RUN apt-get install -y zsh \
    vim

RUN rm -rf ~/.oh-my-zsh/custom/plugins/zsh-syntax-highlighting
RUN rm -rf ~/.oh-my-zsh/custom/plugins/zsh-history-substring-search

RUN sh -c "$(curl https://raw.githubusercontent.com/deluan/zsh-in-docker/master/zsh-in-docker.sh)" -- \
  -t ys \
  -p https://github.com/zsh-users/zsh-syntax-highlighting \
  -p https://github.com/zsh-users/zsh-history-substring-search

ENV TERM xterm
# set the zsh theme
ENV ZSH_THEME agnoster
